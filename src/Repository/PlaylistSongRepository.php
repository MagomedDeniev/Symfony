<?php

namespace App\Repository;

use App\Entity\PlaylistSong;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlaylistSong|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlaylistSong|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlaylistSong[]    findAll()
 * @method PlaylistSong[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaylistSongRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaylistSong::class);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    public function persist(PlaylistSong $entity): void
    {
        $this->_em->persist($entity);
    }

    public function add(PlaylistSong $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(PlaylistSong $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return PlaylistSong[] Returns an array of PlaylistSong objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PlaylistSong
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
