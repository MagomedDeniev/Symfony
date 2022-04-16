<?php

namespace App\Controller;

use App\CustomAbstracts\CustomAbstractController;
use App\Entity\Bookmark;
use App\Entity\Comment;
use App\Entity\Message;
use App\Entity\PlaylistSong;
use App\Entity\Post;
use App\Entity\Profile;
use App\Entity\Report;
use App\Entity\Song;
use App\Entity\User;
use App\Repository\BookmarkRepository;
use App\Repository\MessageRepository;
use App\Repository\PlaylistSongRepository;
use App\Repository\ReportRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Imagine\Filter\FilterInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Service\FilterService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class JsonController extends CustomAbstractController
{
    #[Route('/songPlaylist/{slug}', name: 'song_playlist', methods: ['GET'])]
    #[Security('is_granted("ROLE_USER")')]
    public function playlist(Song $song, UserRepository $userRepo, PlaylistSongRepository $playlistSongRepo): Response
    {
        $user = $userRepo->findOneBy(['username' => $this->getUser()->getUsername()]);
        $contains = $playlistSongRepo->findOneBy(['user' => $user, 'song' => $song]);

        if ($contains) {
            $user->removePlaylistSong($contains);
            $response = ['status' => 'removed', 'title' => $this->trans('add.to.playlist'), 'message' => $this->trans('flash.removed.from.playlist')];
        } else {
            $playlistSong = new PlaylistSong();
            $playlistSong->setUser($user);
            $playlistSong->setSong($song);
            $playlistSongRepo->persist($playlistSong);
            $response = ['status' => 'added', 'title' => $this->trans('remove.from.playlist'), 'message' => $this->trans('flash.added.to.playlist')];
        }

        $playlistSongRepo->flush();

        return $this->json([
            'response' => $response
        ]);
    }

    #[Route('/postBookmark/{slug}', name: 'post_bookmark', methods: ['GET'])]
    #[Security('is_granted("ROLE_USER")')]
    public function bookmark(Post $post, UserRepository $users, BookmarkRepository $bookmarkRepo): Response
    {
        $user = $users->findOneBy(['username' => $this->getUser()->getUsername()]);
        $contains = $bookmarkRepo->findOneBy(['user' => $user, 'post' => $post]);

        if ($contains) {
            $user->removeBookmark($contains);
            $response = ['status' => 'removed'];
        } else {
            $bookmark = new Bookmark();
            $bookmark->setUser($user);
            $bookmark->setPost($post);
            $bookmarkRepo->persist($bookmark);
            $response = ['status' => 'added'];
        }

        $bookmarkRepo->flush();

        return $this->json([
            'response' => $response
        ]);
    }

    #[Route('/postFeatured/{id}', name: 'post_featured', methods: ['GET'])]
    #[Security('is_granted("ROLE_POST_MODERATOR")')]
    public function featured(Post $post, EntityManagerInterface $em): Response
    {
        if ($post->getFeatured()) {
            $post->setFeatured(false);
            $response = ['status' => 'removed'];
        } else {
            $post->setFeatured(true);
            $response = ['status' => 'added'];
        }

        $em->flush();

        return $this->json([
            'response' => $response
        ]);
    }

    #[Route('/shareSong/{song}/{username}', name: 'share_song', methods: ['GET'])]
    #[Security('is_granted("ROLE_USER")')]
    public function sendSong(Song $song, User $user, MessageRepository $messageRepo): Response
    {
        $message = new Message();
        $message->setSender($this->user());
        $message->setReceiver($user);
        $message->setSong($song);
        $message->setSeen(false);
        $message->setSenderDeleted(false);
        $message->setReceiverDeleted(false);
        $message->setSentAt(new \DateTime('now'));

        $messageRepo->add($message);

        return $this->json([
            'response' => ['status' => 'sent', 'message' => $this->trans('flash.song.is.sent') . ' ' . $user->getUsername()]
        ]);
    }

    #[Route('/sharePost/{post}/{username}', name: 'share_post', methods: ['GET', 'POST'])]
    #[Security('is_granted("ROLE_USER")')]
    public function sendPost(Post $post, User $user, MessageRepository $messageRepo): Response
    {
        $message = new Message();
        $message->setSender($this->user());
        $message->setReceiver($user);
        $message->setPost($post);
        $message->setSenderDeleted(false);
        $message->setReceiverDeleted(false);
        $message->setSentAt(new \DateTime('now'));
        $message->setSeen(false);

        $messageRepo->add($message);

        return $this->json([
            'response' => [
                'status' => 'sent',
                'message' => $this->trans('flash.post.is.sent') . ' ' . $user->getUsername()
            ]
        ]);
    }

    #[Route('/shareUsers', name: 'share_users', methods: ['GET'])]
    #[Security('is_granted("ROLE_USER")')]
    public function shareUsers(UserRepository $userRepo, UploaderHelper $helper): Response
    {
        $shareUsers = [];

        foreach ($userRepo->findShareUsers(['user' => $this->user(), 'type' => 'following']) as $key => $shareUser) {

            if (str_contains($helper->asset($shareUser->getProfile()), 'avatar.jpg')) {
                $image = '/assets/images/avatar.jpg';
            } else {
                $image = $helper->asset($shareUser->getProfile());
            }

            $shareUsers[$key] = [
                'id' => $shareUser->getId(),
                'username' => $shareUser->getUsername(),
                'image' => $image
            ];
        }

        return $this->json([
            'users' => $shareUsers
        ]);
    }

    #[Route('/shareProfile/{profile}/{id}', name: 'share_profile', methods: ['GET'])]
    #[Security('is_granted("ROLE_USER")')]
    public function sendProfile(Profile $profile, User $user, MessageRepository $messageRepo): Response
    {
        $message = new Message();
        $message->setSender($this->user());
        $message->setReceiver($user);
        $message->setProfile($profile);
        $message->setSenderDeleted(false);
        $message->setReceiverDeleted(false);
        $message->setSentAt(new \DateTime('now'));
        $message->setSeen(false);

        $messageRepo->add($message);

        return $this->json([
            'response' => ['status' => 'sent', 'message' => $this->trans('flash.profile.is.sent') . ' ' . $user->getUsername()]
        ]);
    }

    #[Route('/reportProfile/{id}', name: 'report_profile', methods: ['GET'])]
    #[Security('is_granted("ROLE_USER")')]
    public function profile(User $user, ReportRepository $reportRepo): Response
    {
        $exist = $reportRepo->findOneBy(['sender' => $this->user(), 'profile' => $user]);

        if ($exist) {
            $exist->setSeen(false);
            $reportRepo->flush();
        } else {
            $report = new Report();
            $report->setProfile($user);
            $report->setSender($this->user());
            $report->setAccused($user);
            $reportRepo->add($report);
        }

        $this->addFlash('success', $this->trans('flash.report.is.sent'));

        return $this->redirectToRoute('user_profile', [
            'username' => $user->getUsername()
        ]);
    }

    #[Route('/reportComment/{id}', name: 'report_comment', methods: ['GET'])]
    #[Security('is_granted("ROLE_USER")')]
    public function comment(Comment $comment, ReportRepository $reportRepo): Response
    {
        $exist = $reportRepo->findOneBy(['comment' => $comment]);

        if ($exist) {
            $exist->setSeen(false);
            $reportRepo->flush();
        } else {
            $report = new Report();
            $report->setComment($comment);
            $report->setSender($this->user());
            $report->setAccused($comment->getAuthor());
            $report->setContent($comment->getMessage());

            $reportRepo->add($report);
        }

        $this->addFlash('success', $this->trans('flash.report.is.sent'));

        return $this->redirectToRoute('post_show', [
            'id' => $comment->getPost()->getId()
        ]);
    }
}
