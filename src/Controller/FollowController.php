<?php

namespace App\Controller;

use App\CustomAbstracts\CustomAbstractController;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\FollowRepository;
use App\Repository\NotificationRepository;
use App\Service\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FollowController extends CustomAbstractController
{
    #[Route('/user/{username}/followers/{page<\d+>?1}', name: 'user_followers', methods: ['GET'])]
    public function followers(User $user, $page, Paginator $paginator, FollowRepository $followRepo): Response
    {
        $followed = $followRepo->findOneBy(['follower' => $this->getUser(), 'followed' => $user, 'accepted' => true]);
        if ($followed || $this->getUser() === $user || $user->getClosedAccount() === false || $this->isGranted('ROLE_POST_MODERATOR')) {
            $paginator
                ->setClass(User::class)
                ->setMethod('findFollows')
                ->setCriteria(['user' => $user, 'type' => 'followers', 'accepted' => true])
                ->setParameters(['username' => $user->getUsername()])
                ->setOrder(['id' => 'DESC'])
                ->setLimit(50)
                ->setPage($page)
            ;

            return $this->render('interface/user/follows.html.twig', [
                'follows' => $paginator->getData(),
                'paginator' => $paginator,
                'user' => $user,
                'profile' => $user->getProfile(),
                'type' => 'followers'
            ]);
        } else {
            return $this->redirectToRoute('user_profile', [
                'username' => $user->getUsername()
            ]);
        }
    }

    #[Route('/user/{username}/following/{page<\d+>?1}', name: 'user_following', methods: ['GET'])]
    public function following(User $user, $page, Paginator $paginator, FollowRepository $followRepo): Response
    {
        $followed = $followRepo->findOneBy(['follower' => $this->getUser(), 'followed' => $user, 'accepted' => true]);
        if ($followed || $this->getUser() === $user || $user->getClosedAccount() === false || $this->isGranted('ROLE_POST_MODERATOR')) {
            $paginator
                ->setClass(User::class)
                ->setMethod('findFollows')
                ->setCriteria(['user' => $user, 'type' => 'following', 'accepted' => true])
                ->setParameters(['username' => $user->getUsername()])
                ->setOrder(['id' => 'DESC'])
                ->setLimit(50)
                ->setPage($page)
            ;

            return $this->render('interface/user/follows.html.twig', [
                'follows' => $paginator->getData(),
                'paginator' => $paginator,
                'user' => $user,
                'profile' => $user->getProfile(),
                'type' => 'following'
            ]);
        } else {
            return $this->redirectToRoute('user_profile', [
                'username' => $user->getUsername()
            ]);
        }
    }

    #[Route('/follow/{username}', name: 'user_follow', methods: ['GET'])]
    #[Security('is_granted("ROLE_USER")')]
    public function follow(User $user, NotificationRepository $notificationRepo, FollowRepository $followRepo, EntityManagerInterface $em): Response
    {
        if ($this->user() === $user) {
            return $this->redirectToRoute('user_profile', [
                'username' => $user->getUsername()
            ]);
        }

        $follow = $followRepo->findOneBy(['follower' => $this->user(), 'followed' => $user]);

        if ($follow) {
            $this->user()->removeFollow($follow);
            $response = ['status' => 'removed'];
        } else {
            $follow = $this->user()->addFollow($user);
            if ($user->getClosedAccount()) {
                $follow->setAccepted(false);
                $response = ['status' => 'requested'];
            } else {
                $notification = new Notification();
                $notification->setType('user_follow');
                $notification->setReceiver($user);
                $notification->setFollow($follow);
                $notification->setSender($this->user());

                $follow->setAccepted(true);
                $response = ['status' => 'added'];
                $notificationRepo->persist($notification);
            }
            $followRepo->persist($follow);
        }

        $em->flush();

        return $this->json([
            'response' => $response
        ]);
    }

    #[Route('/unfollow/{username}', name: 'user_unfollow', methods: ['GET'])]
    #[Security('is_granted("ROLE_USER")')]
    public function unfollow(User $user, EntityManagerInterface $em, FollowRepository $followRepo): Response
    {
        $follow = $followRepo->findOneBy(['follower' => $user, 'followed' => $this->user()]);
        $response = ['status' => null];

        if ($follow) {
            $user->removeFollow($follow);
            $response = ['status' => 'removed'];
            $em->flush();
        }

        return $this->json([
            'response' => $response
        ]);
    }

    #[Route('/acceptRequest/{username}', name: 'accept_request', methods: ['GET'])]
    #[Security('is_granted("ROLE_USER")')]
    public function acceptRequest(User $user, EntityManagerInterface $em, FollowRepository $followRepo, NotificationRepository $notificationRepo): Response
    {
        $follow = $followRepo->findOneBy(['follower' => $user, 'followed' => $this->getUser(), 'accepted' => false]);
        if ($follow) {
            $follow->setAccepted(true);

            $notification = new Notification();
            $notification->setType('user_follow');
            $notification->setSender($user);
            $notification->setReceiver($this->user());
            $notification->setFollow($follow);

            $notification2 = new Notification();
            $notification2->setType('user_accepted');
            $notification2->setSender($this->user());
            $notification2->setReceiver($user);
            $notification2->setFollow($follow);

            $notificationRepo->persist($notification);
            $notificationRepo->persist($notification2);
            $em->flush();
        }

        return $this->json(['response' => ['request' => 'accepted']]);
    }

    #[Route('/rejectRequest/{username}', name: 'reject_request', methods: ['GET'])]
    #[Security('is_granted("ROLE_USER")')]
    public function rejectRequest(User $user, FollowRepository $followRepo): Response
    {
        $follow = $followRepo->findOneBy(['follower' => $user, 'followed' => $this->getUser()]);
        if ($follow) {
            $followRepo->remove($follow);
        }

        return $this->json(['response' => ['request' => 'rejected']]);
    }
}
