<?php

namespace App\Twig;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\FollowRepository;
use App\Repository\UserRepository;
use App\Service\Defender;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UserExtension extends AbstractExtension
{
    private $users;
    private $security;
    private $defender;
    private $follows;

    public function __construct(UserRepository $users, Security $security, Defender $defender, FollowRepository $follows)
    {
        $this->users = $users;
        $this->security = $security;
        $this->defender = $defender;
        $this->follows = $follows;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('actionMessage', [$this, 'actionMessage'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('actionView', [$this, 'actionView'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('notificationMessage', [$this, 'notificationMessage'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('notificationView', [$this, 'notificationView'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('userLine', [$this, 'userLine'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('userIsFollowed', [$this, 'userIsFollowed'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function userLine(Environment $twig, User $follower, $unfollow = false, $request = false): string
    {
        return $twig->render('interface/layouts/user/user_line.html.twig', [
            'follower' => $follower,
            'unfollow' => $unfollow,
            'request' => $request
        ]);
    }

    public function userIsFollowed(User $follower): string
    {
        if ($this->follows->findOneBy(['follower' => $this->getUser(), 'followed' => $follower, 'accepted' => true])) {
            $followed = 'followed';
        } else if ($this->follows->findOneBy(['follower' => $this->getUser(), 'followed' => $follower, 'accepted' => false])) {
            $followed = 'requested';
        } else {
            $followed = 'unfollowed';
        }

        return $followed;
    }

    private function getUser()
    {
        if ($this->defender->isGranted($this->security->getUser(),'ROLE_GUEST')) {
            return $this->security->getUser();
        } else {
            return $this->users->findOneBy(['username' => $this->security->getUser()->getUsername()]);
        }
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function actionMessage(Environment $twig, $action): string
    {
        return $twig->render('interface/layouts/user/action_message.html.twig', [
            'action' => $action
        ]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function actionView(Environment $twig, $action): string
    {
        return $twig->render('interface/layouts/user/action_view.html.twig', [
            'action' => $action
        ]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function notificationMessage(Environment $twig, Notification $notification): string
    {
        return $twig->render('interface/layouts/user/notification_message.html.twig', [
            'notification' => $notification,
            'sender' => $notification->getSender()
        ]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function notificationView(Environment $twig, Notification $notification): string
    {
        return $twig->render('interface/layouts/user/notification_view.html.twig', [
            'notification' => $notification,
            'sender' => $notification->getSender()
        ]);
    }
}
