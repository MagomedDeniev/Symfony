<?php

namespace App\Repository;

use App\Entity\Comment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    public function persist(Comment $entity): void
    {
        $this->_em->persist($entity);
    }

    public function add(Comment $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Comment $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function getNoParentComments($criteria, $orderBy = ['id' => 'DESC'], $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('c');

        $qb->leftJoin('c.parent','p')
            ->where('p.id is null')
        ;

        foreach ($criteria as $property => $value) {
            if ($property == 'post') {
                $qb ->andWhere('c.post = :post')
                    ->setParameter('post', $criteria['post']);
            } elseif ($property == 'song') {
                $qb ->andWhere('c.song = :song')
                    ->setParameter('song', $criteria['song']);
            } else {
                $qb ->andWhere('s.'. $property .' = :' . $property . '')
                    ->setParameter($property,$value)
                ;
            }
        }

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('c.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    // /**
    //  * @return Comment[] Returns an array of Comment objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Comment
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
