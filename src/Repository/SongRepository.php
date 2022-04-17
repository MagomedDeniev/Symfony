<?php

namespace App\Repository;

use App\Entity\Song;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr;

/**
 * @method Song|null find($id, $lockMode = null, $lockVersion = null)
 * @method Song|null findOneBy(array $criteria, array $orderBy = null)
 * @method Song[]    findAll()
 * @method Song[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SongRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Song::class);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    public function persist(Song $entity): void
    {
        $this->_em->persist($entity);
    }

    public function add(Song $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Song $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findPendingSongs($criteria, $orderBy = ['id' => 'DESC'], $limit = null, $offset = 0)
    {
        $qb = $this->createQueryBuilder('s');

        foreach ($criteria as $property => $value) {
            if ($property == 'user') {
                $qb
                    ->where('s.author != :' . $property . '')
                    ->setParameter($property,$value)
                ;
            }
        }

        $qb->andWhere('s.status IS null');

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('s.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function findByKeyword($keyword, $orderBy = ['id' => 'DESC'], $limit = null, $offset = 0)
    {
        $qb = $this->createQueryBuilder('s');

        $qb ->where('s.search LIKE :keyword')
            ->andWhere('s.status = true')
            ->setParameter('keyword','%'. $keyword .'%')
        ;

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('s.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function findUserPlaylist($criteria, $orderBy = ['id' => 'DESC'], $limit = 10, $offset = 0)
    {
        $qb = $this->createQueryBuilder('s');

        foreach ($criteria as $property => $value) {
            if ($property == 'user') {
                $qb
                    ->join('s.playlistSongs', 'us')
                    ->join('us.user', 'u')
                    ->where('s.status = true')
                    ->andWhere('u = :' . $property . '')
                    ->setParameter($property,$value)
                ;
            } else {
                $qb ->andWhere('s.'. $property .' = :' . $property . '')
                    ->setParameter($property,$value)
                ;
            }
        }

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('us.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function getSongViews(Song $song)
    {
        $qb = $this->createQueryBuilder('s');

        $qb->join('s.views','v')
            ->select('SUM(v.quantity) / 3')
            ->where('s.slug = :slug')
            ->setParameter('slug',$song->getSlug())
        ;

        $qb ->setMaxResults(1);

        $result = $qb->getQuery()->getResult()[0]['1'];

        if ($result === null) {
            $result = 0;
        }

        return round($result);
    }

    public function findByViews($criteria, $orderBy = null, $limit = null, $offset = 0)
    {
        $date = (new \DateTime('now'))->modify('-21 day')->format('Y-m-d');

        $qb = $this->createQueryBuilder('s');

        $qb->join('s.views','v',Expr\Join::WITH,'v.viewedAt > \'' . $date . '\'')
            ->groupBy('s')
            ->orderBy('COUNT(v.id)','DESC')
        ;

        foreach ($criteria as $property => $value) {
            $qb ->andWhere('s.'. $property .' = :' . $property . '')
                ->setParameter($property,$value)
            ;
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function findByDiscussed($criteria, $orderBy = null, $limit = null, $offset = 0)
    {
        $date = (new \DateTime('now'))->modify('-7 day')->format('Y-m-d');

        $qb = $this->createQueryBuilder('s');

        $qb ->join('s.comments','c',Expr\Join::WITH,'c.publishedAt > \'' . $date . '\'')
            ->groupBy('s')
            ->orderBy('COUNT(c.id)','DESC')
        ;

        foreach ($criteria as $property => $value) {
            $qb ->andWhere('s.'. $property .' = :' . $property . '')
                ->setParameter($property,$value)
            ;
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function findByTags($tags, $orderBy = ['id' => 'DESC'], $limit = null, $offset = 0)
    {
        $qb = $this->createQueryBuilder('s');

        foreach ($tags as $key => $tag) {
            $qb ->innerJoin('s.tags','t' . $key)
                ->andWhere('t' . $key . '.slug IN (:tag' . $key . ')')
                ->setParameter('tag' . $key, $tag)
            ;
        }

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('s.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Song
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
