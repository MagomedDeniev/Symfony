<?php

namespace App\Repository;

use App\Entity\EmailAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EmailAddress|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmailAddress|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmailAddress[]    findAll()
 * @method EmailAddress[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmailAddressRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailAddress::class);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    public function persist(EmailAddress $entity): void
    {
        $this->_em->persist($entity);
    }

    public function add(EmailAddress $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(EmailAddress $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return EmailAddress[] Returns an array of EmailAddress objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EmailAddress
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
