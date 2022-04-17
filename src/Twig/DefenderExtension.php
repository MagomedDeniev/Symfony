<?php

namespace App\Twig;

use App\Entity\Comment;
use App\Entity\Song;
use App\Entity\User;
use App\Service\Defender;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DefenderExtension extends AbstractExtension
{
    public function __construct(private Defender $defender){}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_role', [$this, 'is_role'], ['is_safe' => ['html']]),
            new TwigFunction('rightToChangeUserRights', [$this, 'rightToChangeUserRights'], ['is_safe' => ['html']]),
            new TwigFunction('rightToDeleteComment', [$this, 'rightToDeleteComment'], ['is_safe' => ['html']]),
            new TwigFunction('rightToBlockUser', [$this, 'rightToBlockUser'], ['is_safe' => ['html']]),
            new TwigFunction('rightToEditSong', [$this, 'rightToEditSong'], ['is_safe' => ['html']]),
            new TwigFunction('hasOnlyAuthorRightsInSongs', [$this, 'hasOnlyAuthorRightsInSongs'], ['is_safe' => ['html']]),
            new TwigFunction('rightToEditSongs', [$this, 'rightToEditSongs'], ['is_safe' => ['html']]),
        ];
    }

    public function is_role(User $user, $role): bool
    {
        return $this->defender->isGranted($user, $role);
    }

    public function rightToChangeUserRights($moderator, User $user): bool
    {
        return $this->defender->rightToChangeUserRights($moderator, $user);
    }

    public function rightToDeleteComment($user, Comment $comment): bool
    {
        return $this->defender->rightToDeleteComment($user, $comment);
    }

    public function rightToBlockUser($moderator, User $user): bool
    {
        return $this->defender->rightToBlockUser($moderator,$user);
    }

    public function rightToEditSongs(User $user): bool
    {
        return $this->defender->rightToEditSongs($user);
    }

    public function rightToEditSong(Song $song): bool
    {
        return $this->defender->rightToEditSong($song);
    }

    public function hasOnlyAuthorRightsInSongs(User $user): bool
    {
        return $this->defender->hasOnlyAuthorRightsInSongs($user);
    }
}
