<?php

namespace App\Repository;

use App\Entity\Message;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Message|null find($id, $lockMode = null, $lockVersion = null)
 * @method Message|null findOneBy(array $criteria, array $orderBy = null)
 * @method Message[]    findAll()
 * @method Message[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    public function persist(Message $entity): void
    {
        $this->_em->persist($entity);
    }

    public function add(Message $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(Message $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function findConversations($criteria, $orderBy = ['sentAt' => 'ASC'], $limit = null, $offset = 0)
    {
        $qb = $this->createQueryBuilder('m');

        $qb ->where('m.sender = :user')
            ->orWhere('m.receiver = :user')
            ->setParameter('user',$criteria['user'])
        ;

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('m.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function findConversation($criteria, $orderBy = ['sentAt' => 'ASC'], $limit = null, $offset = 0)
    {
        $qb = $this->createQueryBuilder('m');

        $qb ->where('m.sender = :user_one AND m.sender_deleted = false AND m.receiver = :user_two')
            ->orWhere('m.receiver = :user_one AND m.receiver_deleted = false AND m.sender = :user_two')
            ->setParameter('user_one',$criteria['user_one'])
            ->setParameter('user_two',$criteria['user_two'])
        ;

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('m.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function findConversationMessagesCount($criteria, $orderBy = ['sentAt' => 'ASC'], $limit = null, $offset = 0)
    {
        $qb = $this->createQueryBuilder('m');

        $qb ->select('count(m.id) as count')
            ->where('m.sender = :user_one AND m.sender_deleted = false AND m.receiver = :user_two')
            ->orWhere('m.receiver = :user_one AND m.receiver_deleted = false AND m.sender = :user_two')
            ->setParameter('user_one',$criteria['user_one'])
            ->setParameter('user_two',$criteria['user_two'])
        ;

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('m.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    // /**
    //  * @return Message[] Returns an array of Message objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Message
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
