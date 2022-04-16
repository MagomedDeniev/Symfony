<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    public function persist(Post $entity): void
    {
        $this->_em->persist($entity);
    }

    public function add(Post $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Post $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findRecommendations($criteria, $orderBy = ['id' => 'DESC'], $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('p');

        foreach ($criteria as $property => $value) {
            if ($property == 'gender') {
                $qb ->where('p.gender = :gender OR p.gender IS NULL')
                    ->setParameter('gender',$value)
                ;
            } else {
                $qb ->andWhere('p.'. $property .' = :' . $property . '')
                    ->setParameter($property,$value)
                ;
            }
        }

        $qb->join('p.author','u')
            ->andWhere('u.closedAccount = false')
        ;

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('p.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function findFeedPosts($criteria, $orderBy = ['id' => 'DESC'], $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('p');

        foreach ($criteria as $property => $value) {
            if ($property == 'following') {
                $qb->where('p.author IN (:following)')
                    ->setParameter('following',$value)
                ;
            } else if ($property == 'user')  {
                $qb->join('p.author','u')
                    ->join('u.followers', 'f')
                    ->andWhere('f.follower = :user')
                    ->andWhere('f.accepted = true')
                    ->setParameter('user', $criteria['user'])
                ;
            } else {
                $qb ->andWhere('p.'. $property .' = :' . $property . '')
                    ->setParameter($property,$value)
                ;
            }
        }

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('p.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function findUserTaggedPosts($criteria, $orderBy = ['id' => 'DESC'], $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('p');

        $qb->join('p.taggedUsers', 'u')
            ->where('u.id IN (:user)')
            ->andWhere('p.status = true')
            ->setParameter('user', $criteria['user'])
        ;

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('p.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function findByKeyword($keyword, $orderBy = ['id' => 'DESC'], $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('p');

        $qb ->join('p.author', 'u')
            ->where('p.content LIKE :keyword')
            ->orWhere('p.title LIKE :keyword')
            ->andWhere('p.status = true')
            ->andWhere('u.closedAccount = false')
            ->setParameter('keyword','%'. $keyword .'%')
        ;

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('p.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function findUserBookmarks($criteria, $orderBy = ['id' => 'DESC'], $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('p');

        foreach ($criteria as $property => $value) {
            if ($property == 'user') {
                $qb
                    ->join('p.bookmarks', 'b')
                    ->join('b.user', 'u')
                    ->where('p.status = true')
                    ->andWhere('u = :' . $property . '')
                    ->setParameter($property,$value)
                ;
            } else {
                $qb ->andWhere('p.'. $property .' = :' . $property . '')
                    ->setParameter($property,$value)
                ;
            }
        }

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('b.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function findPosts($criteria = [], $orderBy = ['id' => 'DESC'], $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('p');

        foreach ($criteria as $key => $value) {
            if ($key == 'tag') {
                $qb ->join('p.tags', 't')
                    ->andWhere('t.slug = :' . $key . '')
                    ->setParameter($key,$value->getSlug())
                ;
            } else {
                $qb->andWhere('p.'. $key .' = :' . $key . '')
                    ->setParameter($key,$value)
                ;
            }
        }

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('p.'. $key, $value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
