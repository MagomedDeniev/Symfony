<?php

namespace App\Controller;

use App\CustomAbstracts\CustomAbstractController;
use App\Entity\Action;
use App\Entity\Report;
use App\Entity\Song;
use App\Entity\User;
use App\Repository\ActionRepository;
use App\Repository\EmailAddressRepository;
use App\Repository\NotificationRepository;
use App\Repository\PeopleRepository;
use App\Repository\PostRepository;
use App\Repository\ReportRepository;
use App\Repository\SongRepository;
use App\Repository\UserRepository;
use App\Service\Defender;
use DateTime;
use App\Entity\Post;
use App\Entity\Notification;
use App\Form\NotificationType;
use App\Service\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/moderation', name: 'moderation_')]
class ModerationController extends CustomAbstractController
{
    #[Route('/', name: 'index',  methods: ['GET'])]
    #[Security('is_granted("ROLE_OWNER")')]
    public function index(PostRepository $posts, SongRepository $songs, UserRepository $users, PeopleRepository $people): Response
    {
        $stats = [
            'users' => [
                'name' => 'users',
                'moderation' => null,
                'published' => null,
                'total' => $users->count([])
            ],
            'posts' => [
                'name' => 'posts',
                'moderation' => $posts->count(['status' => false]),
                'published' => $posts->count(['status' => true]),
                'total' => $posts->count([])
            ],
            'songs' => [
                'name' => 'songs',
                'moderation' => $songs->count(['status' => false]),
                'published' => $songs->count(['status' => true]),
                'total' => $songs->count([])
            ],
            'people' => [
                'name' => 'people',
                'moderation' => null,
                'published' => null,
                'total' => $people->count([])
            ]
        ];

        return $this->render('interface/moderation/index.html.twig', [
            'stats' => $stats
        ]);
    }

    #[Route('/music/{page<\d+>?1}', name: 'music', methods: ['GET'])]
    #[Security('is_granted("ROLE_SONG_AUTHOR") or is_granted("ROLE_SONG_MODERATOR")')]
    public function music($page, Paginator $paginator): Response
    {
        $paginator
            ->setClass(Song::class)
            ->setType('song')
            ->setPage($page)
            ->setLimit(15)
            ->setOrder(['publicationDate' => 'DESC'])
            ->setCriteria(['author' => $this->user(), 'status' => null])
        ;

        return $this->render('interface/moderation/songs.html.twig', [
            'songs' => $paginator->getData(),
            'paginator' => $paginator
        ]);
    }

    #[Route('/reports/{page<\d+>?1}', name: 'reports',  methods: ['GET'])]
    #[Security('is_granted("ROLE_REPORT_MODERATOR")')]
    public function reports($page, Paginator $paginator, ReportRepository $reportRepo): Response
    {
        $paginator
            ->setClass(Report::class)
            ->setPage($page)
            ->setLimit(20)
            ->setOrder(['id' => 'DESC'])
        ;

        foreach ($reportRepo->findBy(['seen' => false]) as $report) {
            $report->setSeen(true);
        }

        $reportRepo->flush();

        return $this->render('interface/moderation/reports.html.twig', [
            'reports' => $paginator->getData(),
            'paginator' => $paginator
        ]);
    }

    #[Route('/ready/{page<\d+>?1}', name: 'ready',  methods: ['GET'])]
    #[Security('is_granted("ROLE_SONG_MODERATOR")')]
    public function ready($page, Paginator $paginator): Response
    {
        $paginator
            ->setClass(Song::class)
            ->setType('song')
            ->setPage($page)
            ->setLimit(15)
            ->setOrder(['publicationDate' => 'DESC'])
            ->setCriteria(['status' => false])
        ;

        return $this->render('interface/moderation/songs.html.twig', [
            'songs' => $paginator->getData(),
            'paginator' => $paginator
        ]);
    }

    #[Route('/pending/{page<\d+>?1}', name: 'pending',  methods: ['GET'])]
    #[Security('is_granted("ROLE_SONG_MODERATOR")')]
    public function pending($page, Paginator $paginator): Response
    {
        $paginator
            ->setClass(Song::class)
            ->setType('song')
            ->setPage($page)
            ->setLimit(15)
            ->setOrder(['publicationDate' => 'DESC'])
            ->setMethod('findPendingSongs')
            ->setCriteria(['user' => $this->user()])
        ;

        return $this->render('interface/moderation/songs.html.twig', [
            'songs' => $paginator->getData(),
            'paginator' => $paginator
        ]);
    }

    #[Route('/actions/{page<\d+>?1}', name: 'actions',  methods: ['GET'])]
    #[Security('is_granted("ROLE_USER_ACTIONS")')]
    public function actions($page, Paginator $paginator, Defender $defender): Response
    {
        $paginator
            ->setOrder(['createdAt' => 'DESC'])
            ->setClass(Action::class)
            ->setType('action')
            ->setLimit(20)
            ->setPage($page);

        return $this->render('interface/moderation/actions.html.twig', [
            'actions' => $paginator->getData(),
            'paginator' => $paginator,
            'moderators' => $defender->getActionUsers()
        ]);
    }

    #[Route('/actions/user/{username}/{page<\d+>?1}', name: 'user_actions',  methods: ['GET'])]
    #[Security('is_granted("ROLE_USER_ACTIONS")')]
    public function userActions(User $user,$page, Paginator $paginator, Defender $defender): Response
    {
        $paginator
            ->setCriteria(['moderator' => $user])
            ->setParameters(['username' => $user->getUsername()])
            ->setOrder(['createdAt' => 'DESC'])
            ->setClass(Action::class)
            ->setType('action')
            ->setLimit(20)
            ->setPage($page);

        return $this->render('interface/moderation/actions.html.twig', [
            'actions' => $paginator->getData(),
            'paginator' => $paginator,
            'user' => $user,
            'moderators' => $defender->getActionUsers()
        ]);
    }

    #[Route('/actions/type/{type}/{page<\d+>?1}', name: 'type_actions',  methods: ['GET'])]
    #[Security('is_granted("ROLE_USER_ACTIONS")')]
    public function typeActions($type,$page, Paginator $paginator, Defender $defender): Response
    {
        $paginator
            ->setCriteria(['type' => $type])
            ->setOrder(['createdAt' => 'DESC'])
            ->setClass(Action::class)
            ->setType('action')
            ->setLimit(20)
            ->setPage($page);

        return $this->render('interface/moderation/actions.html.twig', [
            'actions' => $paginator->getData(),
            'paginator' => $paginator,
            'moderators' => $defender->getActionUsers(),
            'type' => $type
        ]);
    }

    #[Route('/action/{id}/delete', name: 'action_delete',  methods: ['POST'])]
    #[Security('is_granted("ROLE_OWNER")')]
    public function deleteAction(Request $request, Action $action, ActionRepository $actionRepo): Response
    {
        if ($this->isCsrfTokenValid('delete'.$action->getId(), $request->request->get('_token'))) {
            $actionRepo->remove($action);
        }

        return $this->redirectToRoute('moderation_actions');
    }

    #[Route('/posts/{page<\d+>?1}', name: 'posts',  methods: ['GET'])]
    #[Security('is_granted("ROLE_POST_MODERATOR")')]
    public function posts($page, Paginator $paginator): Response
    {
        $paginator
            ->setClass(Post::class)
            ->setOrder(['updatedAt' => 'ASC'])
            ->setCriteria(['status' => null])
            ->setLimit(10)
            ->setPage($page)
        ;

        return $this->render('interface/moderation/posts.html.twig', [
            'posts' => $paginator->getData(),
            'paginator' => $paginator
        ]);
    }

    #[Route('/validation/post/{id}', name: 'post_validation',  methods: ['POST'])]
    #[Security('is_granted("ROLE_POST_MODERATOR")')]
    public function postValidation(
        Request $request,
        Post $post,
        NotificationRepository $notificationRepo,
        ActionRepository $actionRepo,
        UserRepository $userRepo,
        EntityManagerInterface $em
    ): Response
    {
        if ($post->getStatus() === null) {
            $sender = $userRepo->findOneBy(['username' => 'shovdanyist']);

            $notification = new Notification();
            $notification->setReceiver($post->getAuthor());
            $notification->setPost($post);
            $notification->setSender($sender);

            $form = $this->createForm(NotificationType::class, $notification);
            $form->handleRequest($request);

            $action = new Action();
            $action->setModerator($this->user());
            $action->setPost($post);

            if ($form->get('approve')->isClicked()) {
                $notification->setType('post_approved');
                $action->setType('post_approved');
                $post->setStatus(true);
                $post->setGender($form->get('gender')->getData());

                if ($post->getTaggedUsers()) {
                    foreach ($post->getTaggedUsers()->getValues() as $user) {
                        $notify = new Notification();
                        $notify->setReceiver($user);
                        $notify->setPost($post);
                        $notify->setSender($post->getAuthor());
                        $notify->setType('user_tagged');
                        $notificationRepo->persist($notify);
                    }
                }

                if ($post->getNotifications()) {
                    foreach ($post->getNotifications() as $notification) {
                        $notification->setStatus(true);
                    }
                }

                if ($post->getPublishedAt()->getTimestamp() === $post->getUpdatedAt()->getTimestamp()) {
                    $post->setPublishedAt(new DateTime('now'));
                }
            } elseif ($form->get('reject')->isClicked()) {
                if (!$form->get('message')->getData()) {
                    $this->addFlash('danger', 'Вы не указали причину отказа');
                    return $this->redirectToRoute('moderation_posts');
                }

                $notification->setType('post_rejected');
                $action->setContent($notification->getMessage());
                $action->setType('post_rejected');
                $post->setStatus(false);
            }

            $actionRepo->persist($action);
            $notificationRepo->persist($notification);
            $em->flush();
        } else {
            ($post->getStatus() === true) ? $status = 'approved' : $status = 'rejected';
            $this->addFlash('info', $this->trans( 'post.is.already.' . $status));
        }

        return $this->redirectToRoute('moderation_posts');
    }

    #[Route('/user/rights/{username}', name: 'user_rights',  methods: ['GET','POST'])]
    #[Security('is_granted("ROLE_SUPER_MODERATOR")')]
    public function userRights(Request $request, User $user, Defender $defender, ActionRepository $actionRepo): Response
    {
        if (!$defender->rightToChangeUserRights($this->user(),$user)) {
            return $this->redirectToRoute('user_profile', ['username' => $user->getUsername()]);
        }

        $form = $this->createFormBuilder($user)->getForm();

        foreach ($defender->getRoles() as $role) {
            if ($defender->isGranted($this->user(),$role)) {
                $form->add($role, CheckboxType::class,[
                    'label' => $role,
                    'mapped' => false,
                    'required' => false,
                    'data' => $defender->isGranted($user,$role),
                    'disabled' => $role === 'ROLE_USER_RIGHTS' && !$defender->isGranted($this->user(),'ROLE_ADMINISTRATOR'),
                    'label_attr' => ['class' => 'switch-custom']
                ]);
            } else {
                $form->add($role, CheckboxType::class,[
                    'label' => $role,
                    'mapped' => false,
                    'required' => false,
                    'data' => $defender->isGranted($user,$role),
                    'disabled' => true,
                    'label_attr' => ['class' => 'switch-custom']
                ]);
            }
        }

        if ($defender->isGranted($this->user(),'ROLE_OWNER')) {
            $form->add('ROLE_ADMINISTRATOR', CheckboxType::class,[
                'label' => 'ROLE_ADMINISTRATOR',
                'mapped' => false,
                'required' => false,
                'data' => $defender->isGranted($user,'ROLE_ADMINISTRATOR'),
                'label_attr' => ['class' => 'switch-custom']
            ]);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $rightsBefore = $user->getRoles();

            $rights = [];

            foreach ($defender->getRoles() as $role) {
                $status = $form->get($role)->getData();
                if ($status) {
                    $rights[] = $role;
                }
            }

            if ($defender->isGranted($this->user(),'ROLE_OWNER') && $form->get('ROLE_ADMINISTRATOR')->getData()) {
                $rights[] = 'ROLE_ADMINISTRATOR';
            }

            ($rights) ? $user->setRoles($rights) : $user->setRoles(["ROLE_USER"]);

            $rightsAfter = $user->getRoles();

            $message = '';

            foreach (array_diff($rightsBefore, $rightsAfter) as $value) {
                $message .= '<br> - ' . $this->trans($value);
            }

            foreach (array_diff($rightsAfter, $rightsBefore) as $value) {
                $message .= '<br> + ' . $this->trans($value);
            }

            if ($message !== '') {
                $action = new Action();
                $action->setModerator($this->user());
                $action->setUser($user);
                $action->setType('rights_changed');
                $action->setContent($message);
                $actionRepo->add($action);
            }

            return $this->redirectToRoute('user_profile', [
                'username' => $user->getUsername()
            ]);
        }

        return $this->render('interface/moderation/rights.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'roles' => $defender->getRoles()
        ]);
    }

    #[Route('/banUser/{id}', name: 'ban_user',  methods: ['POST'])]
    #[Security('is_granted("ROLE_USER_BAN")')]
    public function banUser(User $user, EmailAddressRepository $emails, Defender $defender, ActionRepository $actionRepo): Response
    {
        if (!$defender->rightToBlockUser($this->user(),$user)) {
            return $this->redirectToRoute('user_profile', ['username' => $user->getUsername()]);
        }

        $email = $emails->findOneBy(['address' => $user->getConfirmedEmail()]);

        if ($email) {
            $email->setStatus(false);
        }

        $user->setStatus(false);
        $user->setRoles(["ROLE_USER"]);

        $action = new Action();
        $action->setModerator($this->user());
        $action->setUser($user);
        $action->setType('user_blocked');

        $actionRepo->add($action);

        $this->addFlash('danger', $this->trans('user.is.banned',['username' => $user->getUsername()]));

        return $this->redirectToRoute('user_profile', [
            'username' => $user->getUsername()
        ]);
    }

    #[Route('/unbanUser/{id}', name: 'unban_user',  methods: ['POST'])]
    #[Security('is_granted("ROLE_USER_BAN")')]
    public function unbanUser(User $user, EmailAddressRepository $emails, ActionRepository $actionRepo): Response
    {
        $email = $emails->findOneBy(['address' => $user->getConfirmedEmail()]);

        if ($email) {
            $email->setStatus(true);;
        }

        $user->setStatus(true);

        $action = new Action();
        $action->setModerator($this->user());
        $action->setUser($user);
        $action->setType('user_unblocked');

        $actionRepo->add($action);

        $this->addFlash('success', $this->trans('user.is.unbanned',['username' => $user->getUsername()]));

        return $this->redirectToRoute('user_profile', [
            'username' => $user->getUsername()
        ]);
    }
}
