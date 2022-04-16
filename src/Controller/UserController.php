<?php

namespace App\Controller;

use App\CustomAbstracts\CustomAbstractController;
use App\Entity\Follow;
use App\Entity\Profile;
use App\Entity\Song;
use App\Entity\Notification;
use App\Entity\Post;
use App\Entity\User;
use App\Form\NewUserType;
use App\Form\ResetPasswordType;
use App\Form\ProfileType;
use App\Form\SettingsType;
use App\Repository\EmailAddressRepository;
use App\Repository\FollowRepository;
use App\Repository\MessageRepository;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use App\Service\Defender;
use App\Service\Initializer;
use App\Service\Mailer;
use App\Service\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Vich\UploaderBundle\Handler\UploadHandler;

class UserController extends CustomAbstractController
{
    #[Route('/users/{page<\d+>?1}', name: 'users_index', methods: ['GET'])]
    #[Security('is_granted("ROLE_OWNER")')]
    public function index($page, Paginator $paginator): Response
    {
        $paginator
            ->setClass(User::class)
            ->setLimit(20)
            ->setOrder(['registeredAt' => 'DESC'])
            ->setPage($page)
        ;

        return $this->render('interface/user/index.html.twig', [
            'users' => $paginator->getData(),
            'paginator' => $paginator
        ]);
    }

    #[Route('/user/new', name: 'user_new', methods: ['GET','POST'])]
    #[Security('is_granted("ROLE_OWNER")')]
    public function new(Request $request, UserPasswordHasherInterface $userPasswordHash, UserRepository $userRepos): Response
    {
        $user = new User();
        $form = $this->createForm(NewUserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword(
                $userPasswordHash->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );

            $profile = new Profile();
            $user->setProfile($profile);
            $user->getProfile()->setGender(0);
            $user->getProfile()->setAvatar('avatar.jpg');
            $userRepos->add($user);

            $this->addFlash('success', 'Регистрация успешно завершена');

            return $this->redirectToRoute('users_index');
        }

        return $this->render('interface/user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/user/{username}/{page<\d+>?1}', name: 'user_profile', methods: ['GET'])]
    public function profile(User $user, $page, Paginator $paginator, Defender $defender, Initializer $initializer, UserRepository $userRepo): Response
    {
        $this->updateLastActivity();
        if (!$defender->isGranted($this->getUser(),'ROLE_GUEST') && $user === $this->user() || $this->isGranted('ROLE_POST_MODERATOR')) {
            $criteria = ['author' => $user];
        } else {
            $criteria = ['author' => $user, 'status' => true];
        }

        $paginator
            ->setClass(Post::class)
            ->setOrder(['publishedAt' => 'DESC'])
            ->setCriteria($criteria)
            ->setParameters(['username' => $user->getUsername()])
            ->setLimit(30)
            ->setPage($page)
        ;

        $follows = $initializer->initializeProfile($user);

        return $this->render('interface/user/profile.html.twig', [
            'invitees' => $userRepo->count(['invitedBy' => $user, 'status' => true]),
            'posts' => $paginator->getData(),
            'paginator' => $paginator,
            'profile' => $user->getProfile(),
            'user' => $user,
            'type' => 'profile',
            'shareUsers' => $follows['sharedUsers'],
            'followers' => $follows['followers'],
            'following' => $follows['following']
        ]);
    }

    #[Route('/user/{username}/list/{page<\d+>?1}', name: 'user_list', methods: ['GET'])]
    public function listProfile(User $user, $page, Paginator $paginator, Defender $defender, Initializer $initializer, UserRepository $userRepo): Response
    {
        $this->updateLastActivity();
        if (!$defender->isGranted($this->getUser(),'ROLE_GUEST') && $user === $this->user() || $this->isGranted('ROLE_POST_MODERATOR')) {
            $criteria = ['author' => $user];
        } else {
            $criteria = ['author' => $user, 'status' => true];
        }

        $paginator
            ->setClass(Post::class)
            ->setOrder(['publishedAt' => 'DESC'])
            ->setCriteria($criteria)
            ->setParameters(['username' => $user->getUsername()])
            ->setLimit(15)
            ->setPage($page)
        ;

        $follows = $initializer->initializeProfile($user);

        return $this->render('interface/user/profile.html.twig', [
            'invitees' => $userRepo->count(['invitedBy' => $user, 'status' => true]),
            'posts' => $paginator->getData(),
            'paginator' => $paginator,
            'profile' => $user->getProfile(),
            'user' => $user,
            'type' => 'list',
            'shareUsers' => $follows['sharedUsers'],
            'followers' => $follows['followers'],
            'following' => $follows['following']
        ]);
    }

    #[Route('/user/{username}/tagged/{page<\d+>?1}', name: 'user_tagged', methods: ['GET'])]
    public function tagged(User $user, $page, Paginator $paginator, Initializer $initializer, UserRepository $userRepo): Response
    {
        $paginator
            ->setClass(Post::class)
            ->setMethod('findUserTaggedPosts')
            ->setOrder(['publishedAt' => 'DESC'])
            ->setCriteria(['user' => $user])
            ->setParameters(['username' => $user->getUsername()])
            ->setLimit(30)
            ->setPage($page)
        ;

        $follows = $initializer->initializeProfile($user);

        return $this->render('interface/user/profile.html.twig', [
            'invitees' => $userRepo->count(['invitedBy' => $user, 'status' => true]),
            'posts' => $paginator->getData(),
            'paginator' => $paginator,
            'profile' => $user->getProfile(),
            'user' => $user,
            'type' => 'tagged',
            'shareUsers' => $follows['sharedUsers'],
            'followers' => $follows['followers'],
            'following' => $follows['following']
        ]);
    }

    #[Route('/user/{username}/edit', name: 'user_edit', methods: ['GET','POST'])]
    #[Security('is_granted("ROLE_USER")')]
    public function edit(Request $request, User $user, UploadHandler $handler, EmailAddressRepository $emailAddressRepo, EntityManagerInterface $em): Response
    {
        if ($user !== $this->user() && !$this->isGranted('ROLE_OWNER')) {
            return $this->redirectToRoute('user_edit', [
                'username' => $this->user()->getUsername()
            ]);
        }

        $form = $this->createForm(ProfileType::class, $user->getProfile());

        if ($this->isGranted('ROLE_OWNER')) {
            $form->add('verified', CheckboxType::class, [
                'label' => 'verified',
                'required' => false,
                'label_attr' => ['class' => 'switch-custom']
            ]);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('avatarDelete')->getData() == true) {
                $handler->remove($user->getProfile(),'avatarFile');
                $user->getProfile()->setAvatar('avatar.jpg');
            }

            if ($user->getProfile()->getBirthday() && $user->getConfirmedEmail() && $emailAddressRepo->findOneBy(['address' => $user->getConfirmedEmail()])) {
                if ($user->getProfile()->getBirthday() !== $emailAddressRepo->findOneBy(['address' => $user->getConfirmedEmail()])->getBirthday()) {
                    $emailAddressRepo->findOneBy(['address' => $user->getConfirmedEmail()])->setBirthday($user->getProfile()->getBirthday());
                }
            }

            $em->flush();
            $this->addFlash('success', $this->trans('profile.changes.saved'));
            return $this->redirectToRoute('user_profile', ['username' => $user->getUsername()]);
        }

        return $this->render('interface/user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    #[Route('/user/{username}/settings', name: 'user_settings', methods: ['GET','POST'])]
    #[Security('is_granted("ROLE_USER")')]
    public function settings(
        Request $request,
        User $user,
        Mailer $mailer,
        TokenGeneratorInterface $tokenGenerator,
        Defender $defender,
        UserPasswordHasherInterface $userPasswordHash,
        FollowRepository $followRepo,
        EntityManagerInterface $em): Response
    {
        if ($user !== $this->user() && !$this->isGranted('ROLE_OWNER')) {
            return $this->redirectToRoute('user_settings', [
                'username' => $this->user()->getUsername()
            ]);
        }

        $form = $this->createForm(SettingsType::class, $user,['user' => $user]);

        if ($user->getEmail() != $user->getConfirmedEmail()) {
            $form->get('email')->addError(new FormError($this->trans('email.not.verified')));
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('closedAccount')->getData() === false) {
                $followRequests = $followRepo->findBy(['followed' => $this->user(),'accepted' => false]);
                foreach ($followRequests as $followRequest) {
                    $followRequest->setAccepted(true);
                }
            }

            if ($this->isGranted('ROLE_OWNER') && $user !== $this->user()) {
                $user->setPassword(
                    $userPasswordHash->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
            }

            $verification = $defender->rightToSetUsername($form->get('username')->getData(), $user);

            if ($verification['status'] == true) {
                if ($user->getEmail() != $user->getConfirmedEmail()) {
                    $user->setToken($tokenGenerator->generateToken());
                    $mailer->setTo($form->get('email')->getData())
                        ->setSubject($this->trans('email.confirmation.on.website'))
                        ->setTemplate('interface/layouts/mailer/email_confirmation.html.twig')
                        ->setVariables(['user' => $user])
                        ->notify();

                    if ($user->getConfirmedEmail() && $user->getStatus() !== false) {
                        $user->setStatus(null);
                    }
                } elseif ($user->getEmail() === $user->getConfirmedEmail() && $user->getStatus() === null) {
                    $user->setStatus(true);
                }

                $user->setUsername(strtolower($form->get('username')->getData()));
                $em->flush();

                return $this->redirectToRoute('user_profile', ['username' => $form->get('username')->getData()]);
            } else {
                $form->get('username')->addError(new FormError($verification['message']));
            }
        }

        return $this->render('interface/user/settings.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    #[Route('/reset', name: 'user_reset', methods: ['GET','POST'])]
    #[Security('is_granted("ROLE_USER")')]
    public function reset(Request $request, UserRepository $repo, UserPasswordHasherInterface $userPasswordHash, UserRepository $userRepo): Response
    {
        $user = $repo->findOneBy(['username' => $this->getUser()->getUsername()]);
        $form = $this->createForm(ResetPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($userPasswordHash->isPasswordValid($user,$form->get('current')->getData())) {
                $user->setPassword($userPasswordHash->hashPassword($user, $form->get('new')->getData()));
                $userRepo->persist($user);

                $this->addFlash('success', $this->trans('flash.password.changed'));

                return $this->redirectToRoute('user_profile', ['username' => $user->getUsername()]);
            } else {
                $form->get('current')->addError(new FormError($this->trans('current.password.incorrect')));
            }
        }

        return $this->render('interface/user/reset.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/notifications/{page<\d+>?1}', name: 'user_notifications', methods: ['GET'])]
    #[Security('is_granted("ROLE_USER")')]
    public function notifications(
        $page,
        NotificationRepository $notifyRepo,
        UserRepository $userRepo,
        Paginator $paginator,
        EntityManagerInterface $em,
        FollowRepository $followRepo
    ): Response
    {
        $this->updateLastActivity();
        $user = $userRepo->findOneBy(['username' => $this->getUser()->getUsername()]);
        $requests = $followRepo->count(['followed' => $this->getUser(),'accepted' => false]);

        if ($notifyRepo->count(['receiver' => $user]) > 90) {
            $notifications = $notifyRepo->findBy(['receiver' => $user], ['id' => 'DESC'], null, 90);
            foreach ($notifications as $notification) {
                $user->removeReceivedNotification($notification);
            }
            $em->flush();
        }

        foreach ($notifyRepo->findBy(['receiver' => $user, 'status' => true]) as $notification) {
            $notification->setSeen(true);
        }

        $em->flush();

        $paginator
            ->setClass(Notification::class)
            ->setType('notification')
            ->setOrder(['publishedAt' => 'DESC'])
            ->setCriteria(['receiver' => $user, 'status' => true])
            ->setLimit(30)
            ->setPage($page);

        return $this->render('interface/user/notifications.html.twig', [
            'notifications' => $paginator->getData(),
            'paginator' => $paginator,
            'requests' => $requests
        ]);
    }

    #[Route('/requests/{page<\d+>?1}', name: 'user_requests', methods: ['GET'])]
    #[Security('is_granted("ROLE_USER")')]
    public function requests($page, Paginator $paginator): Response
    {
        $paginator
            ->setClass(Follow::class)
            ->setType('follow')
            ->setCriteria(['followed' => $this->user(), 'accepted' => false])
            ->setLimit(50)
            ->setPage($page);

        return $this->render('interface/user/requests.html.twig', [
            'requests' => $paginator->getData(),
            'paginator' => $paginator
        ]);
    }

    #[Route('/notification/{id}/delete', name: 'user_notification_delete', methods: ['POST'])]
    #[Security('is_granted("ROLE_USER")')]
    public function deleteNotification(Request $request, Notification $notification, NotificationRepository $notificationRepo): Response
    {
        if ($this->user() !== $notification->getReceiver()) {
            return $this->redirectToRoute('app_home');
        }

        if ($this->isCsrfTokenValid('delete'.$notification->getId(), $request->request->get('_token'))) {
            $notificationRepo->remove($notification);
        }

        return $this->redirectToRoute('user_notifications');
    }

    #[Route('/playlist/{page<\d+>?1}', name: 'user_playlist', methods: ['GET'])]
    #[Security('is_granted("ROLE_USER")')]
    public function playlist($page, Paginator $paginator): Response
    {
        $this->updateLastActivity();
        $paginator
            ->setParameters(['username' => $this->user()->getUsername()])
            ->setMethod('findUserPlaylist')
            ->setOrder(['addedAt' => 'DESC'])
            ->setCriteria(['user' => $this->user()])
            ->setClass(Song::class)
            ->setType('playlist')
            ->setLimit(20)
            ->setPage($page);

        return $this->render('interface/user/playlist.html.twig', [
            'playlist' => $paginator->getData(),
            'paginator' => $paginator,
            'user' => $this->user()
        ]);
    }

    #[Route('/bookmarks/{page<\d+>?1}', name: 'user_bookmarks', methods: ['GET'])]
    #[Security('is_granted("ROLE_USER")')]
    public function bookmarks($page, Paginator $paginator, UserRepository $userRepo): Response
    {
        $user = $userRepo->findOneBy(['username' => $this->getUser()->getUsername()]);

        $paginator
            ->setMethod('findUserBookmarks')
            ->setOrder(['addedAt' => 'DESC'])
            ->setCriteria(['user' => $user])
            ->setClass(Post::class)
            ->setType('bookmark')
            ->setLimit(15)
            ->setPage($page)
        ;

        $shareUsers = $userRepo->findShareUsers(['user' => $this->user(), 'type' => 'following']);

        return $this->render('interface/user/bookmarks.html.twig', [
            'posts' => $paginator->getData(),
            'paginator' => $paginator,
            'shareUsers' => $shareUsers
        ]);
    }

    #[Route('/deleteAccount', name: 'user_delete_account', methods: ['GET','POST'])]
    #[Security('is_granted("ROLE_USER")')]
    public function deleteAccount(): Response
    {
        return $this->render('interface/user/delete_account.html.twig');
    }

    #[Route('/userDelete/{id}', name: 'user_delete', methods: ['POST'])]
    #[Security('is_granted("ROLE_USER")')]
    public function delete(Request $request, UserPasswordHasherInterface $userPasswordHash, User $user, MessageRepository $messageRepo, UserRepository $userRepo, EntityManagerInterface $em): Response
    {
        $username = $user->getUsername();

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token')) && $this->isGranted('ROLE_OWNER')) {
            $userRepo->remove($user);
            $this->addFlash('success', $this->trans('flash.user.deleted',['username' => $username]));

            return $this->redirectToRoute('users_index');
        } elseif ($user === $this->user() && $this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token')) && $userPasswordHash->isPasswordValid($user, $request->request->get('password'))) {
            $sentMessages = $messageRepo->findBy(['sender' => $user]);
            $receivedMessages = $messageRepo->findBy(['receiver' => $user]);

            foreach ($sentMessages as $sentMessage) {
                $sentMessage->setReplyTo(null);
            }
            foreach ($receivedMessages as $receivedMessage) {
                $receivedMessage->setReplyTo(null);
            }
            foreach ($user->getInvitees() as $invitee) {
                $invitee->setInvitedBy(null);
            }
            $em->flush();

            $session = new Session();
            $session->invalidate();

            $userRepo->remove($user);

            return $this->render('bundles/TwigBundle/Exception/user_deleted.html.twig');
        }

        $this->addFlash('danger', $this->trans('flash.password.is.wrong'));
        return $this->redirectToRoute('user_delete_account');
    }
}
