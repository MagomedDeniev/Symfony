<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function flush(): void
    {
        $this->_em->flush();
    }

    public function persist(User $entity): void
    {
        $this->_em->persist($entity);
    }

    public function add(User $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    public function remove(User $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws Exception
     */
    public function findConversations($user): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $query = 'SELECT CASE WHEN sender_id = :user
                    THEN receiver_id
                    ELSE sender_id
                    END AS user
                    FROM message
                    WHERE :user IN (sender_id, receiver_id) AND sender_id = :user AND sender_deleted = false
                    OR :user IN (sender_id, receiver_id) AND receiver_id = :user AND receiver_deleted = false
                    GROUP BY user
                    ORDER BY MAX(sent_at) DESC;';

        $statement = $connection->prepare($query);
        $result = $statement->executeQuery(['user' => $user]);

        return $result->fetchAllAssociative();
    }

    public function findLikes($criteria, $orderBy = ['id' => 'DESC'], $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('u');

        $qb->join('u.likes', 'l');

        if (isset($criteria['post'])) {
            $qb->where('l.post = :post');
            $qb->setParameter('post', $criteria['post']);
        } else {
            $qb->where('l.comment = :comment');
            $qb->setParameter('comment', $criteria['comment']);
        }

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('u.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function findFollows($criteria, $orderBy = ['id' => 'DESC'], $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('u');

        if ($criteria['type'] === 'followers') {
            $qb->join('u.following', 'f')
                ->where('f.followed = :user');
        } elseif ($criteria['type'] === 'following') {
            $qb->join('u.followers', 'f')
                ->where('f.follower = :user');
        }

        if (isset($criteria['accepted'])) {
            $qb->andWhere('f.accepted = :accepted')
                ->setParameter('accepted', $criteria['accepted']);
        }

        $qb->setParameter('user', $criteria['user']);

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('f.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function followsCount($criteria)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('count(u)');

        if ($criteria['type'] === 'followers') {
            $qb->join('u.following', 'f')
                ->where('f.followed = :user')
                ->andWhere('f.accepted = true')
            ;
        } elseif ($criteria['type'] === 'following') {
            $qb->join('u.followers', 'f')
                ->where('f.follower = :user')
                ->andWhere('f.accepted = true');
        }

        $qb->setParameter('user', $criteria['user']);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findShareUsers($criteria, $orderBy = ['username' => 'ASC'], $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('u');

        if ($criteria['type'] === 'followers') {
            $qb->join('u.following', 'f')
                ->where('f.followed = :user')
                ->andWhere('f.accepted = true');
        } elseif ($criteria['type'] === 'following') {
            $qb->join('u.followers', 'f')
                ->where('f.follower = :user')
                ->andWhere('f.accepted = true');
        }

        $qb->setParameter('user', $criteria['user']);

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('u.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function findOnlineFollows($criteria, $orderBy = ['id' => 'DESC'], $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('u');

        if ($criteria['type'] === 'followers') {
            $qb->join('u.following', 'f')
                ->where('f.followed = :user')
                ->andWhere('f.accepted = true');
        } elseif ($criteria['type'] === 'following') {
            $qb->join('u.followers', 'f')
                ->where('f.follower = :user')
                ->andWhere('f.accepted = true');
        }

        $date = new \DateTime('-5 minute');

        $qb ->andWhere('u.lastActivityAt > :date')
            ->andWhere('u.hideOnline != true')
            ->setParameter('date', $date)
        ;

        $qb->setParameter('user', $criteria['user']);

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('f.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    public function findByKeyword($keyword, $orderBy = ['id' => 'DESC'], $limit = null, $offset = null)
    {
        $qb = $this->createQueryBuilder('u');

        $qb ->join('u.profile','p')
            ->where('u.username LIKE :keyword')
            ->orWhere('p.fullname LIKE :keyword')
            ->orWhere('p.about LIKE :keyword')
            ->setParameter('keyword','%'. $keyword .'%')
        ;

        foreach ($orderBy as $key => $value) {
            $qb->orderBy('u.'.$key,$value);
        }

        $qb ->setMaxResults($limit)
            ->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return int|mixed|string
     */
    public function findActionUsers()
    {
        $actionRoles = [
            'ROLE_POST_COMMENT_REMOVER',
            'ROLE_SONG_COMMENT_REMOVER',
            'ROLE_PEOPLE_MODERATOR',
            'ROLE_POST_MODERATOR',
            'ROLE_SONG_EDITOR',
            'ROLE_USER_RIGHTS',
            'ROLE_USER_BAN'
        ];

        $qb = $this->createQueryBuilder('u');

        foreach ($actionRoles as $key => $role) {
            $qb->orWhere('u.roles LIKE :role' . $key)
               ->setParameter('role' . $key, '%'. $role .'%');
        }

        return $qb->getQuery()->getResult();
    }

    public function findByRole($role)
    {
        $qb = $this->createQueryBuilder('u');

        $qb->where('u.roles LIKE :role')
            ->orWhere('u.roles LIKE :owner')
            ->setParameter('role', '%'. $role .'%')
            ->setParameter('owner', '%ROLE_OWNER%');

        return $qb->getQuery()->getResult();
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * @param UserInterface $user
     * @param string $newHashedPassword
     */
    public function upgradePassword(UserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function inviteesCount(User $user)
    {
        $date = (new \DateTime('now'))->modify('-3 day')->format('Y-m-d');

        $qb = $this->createQueryBuilder('u');

        $qb->where('u.status = true')
            ->andWhere('u.invitedBy = :invitedBy')
            ->setParameter('invitedBy',$user)
        ;

        return $qb->getQuery()->getResult();
    }
}
