<?php

namespace App\Repository;

use App\Entity\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeopleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    public function persist(Person $entity): void
    {
        $this->_em->persist($entity);
    }

    public function add(Person $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Person $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findPeopleByActivity($activity): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.activity', 'a')
            ->where('a.slug = :activity')
            ->setParameter('activity', $activity)
            ->orderBy('p.firstName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveSongsPerson($slug)
    {
        return $this->createQueryBuilder('p')
            ->join('p.songs', 's')
            ->where('p.slug = :slug')
            ->andWhere('s.status = true')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getSingleResult()
            ;
    }

    public function findActiveSongsPeopleByActivity($person)
    {
        return $this->createQueryBuilder('p')
            ->join('p.activity', 'a')
            ->join('p.songs', 's')
            ->where('a.slug = :person')
            ->andWhere('s.status = true')
            ->setParameter('person', $person)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findPeopleByLetter($letter, $status = null)
    {
        $qb = $this->createQueryBuilder('p');

        $qb->where('p.firstName like :letter')
            ->setParameter('letter', $letter . '%')
            ;

        if ($status) {
            $qb->join('p.songs','s')
               ->andWhere('s.status = :status')
               ->setParameter('status', $status)
            ;
        }

        $qb->orderBy('p.firstName', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Person
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
