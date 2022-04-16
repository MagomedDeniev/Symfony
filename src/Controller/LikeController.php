<?php

namespace App\Controller;

use App\CustomAbstracts\CustomAbstractController;
use App\Entity\Comment;
use App\Entity\Like;
use App\Entity\Notification;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\LikeRepository;
use App\Repository\NotificationRepository;
use App\Service\Paginator;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(methods: ['GET'])]
class LikeController extends CustomAbstractController
{
    #[Route('/post/{id}/likes/{page<\d+>?1}', name: 'post_likes')]
    public function postLikes(Post $post, $page, Paginator $paginator): Response
    {
        $paginator
            ->setClass(User::class)
            ->setMethod('findLikes')
            ->setCriteria(['post' => $post])
            ->setParameters(['id' => $post->getId()])
            ->setOrder(['id' => 'DESC'])
            ->setLimit(30)
            ->setPage($page)
        ;

        return $this->render('interface/post/likes.html.twig', [
            'users' => $paginator->getData(),
            'paginator' => $paginator,
            'post' => $post
        ]);
    }

    #[Route('/comment/{id}/likes/{page<\d+>?1}', name: 'comment_likes')]
    public function commentLikes(Comment $comment, $page, Paginator $paginator): Response
    {
        $paginator
            ->setClass(User::class)
            ->setMethod('findLikes')
            ->setCriteria(['comment' => $comment])
            ->setParameters(['id' => $comment->getId()])
            ->setOrder(['id' => 'DESC'])
            ->setLimit(30)
            ->setPage($page)
        ;

        return $this->render('interface/layouts/comments/likes.html.twig', [
            'users' => $paginator->getData(),
            'paginator' => $paginator,
            'comment' => $comment
        ]);
    }

    #[Route('/postLike/{id}', name: 'post_like')]
    #[Security('is_granted("ROLE_USER")')]
    public function postLike(Post $post, LikeRepository $likeRepo, NotificationRepository $notificationRepo, EntityManagerInterface $em): Response
    {
        $like = $likeRepo->findOneBy(['user' => $this->user(), 'post' => $post]);

        if ($like) {
            $notification = $notificationRepo->findOneBy(['post' => $post, 'type' => 'post_like', 'sender' => $this->user()]);
            if ($notification) {
                $notificationRepo->remove($notification);
            }

            $this->user()->removeLike($like);
            $response = ['status' => 'removed'];
        } else {
            $like = new Like();
            $like->setUser($this->user());
            $like->setPost($post);
            $likeRepo->persist($like);
            $response = ['status' => 'added'];

            if ($post->getAuthor() !== $this->user()) {
                $existNotify = $notificationRepo->findOneBy(['post' => $post, 'type' => 'post_like']);

                if ($existNotify) {
                    if ($existNotify->getSeen()) {
                        $existNotify->setSeen(false);
                        $existNotify->setQuantity(1);
                    } elseif ($existNotify->getSender() !== $this->user() || $existNotify->getQuantity() > 1) {
                        $existNotify->setQuantity($existNotify->getQuantity() + 1);
                    }
                    $existNotify->setPublishedAt(new \DateTime('now'));
                    $existNotify->setSender($this->user());
                } else {
                    $notification = new Notification();
                    $notification->setType('post_like');
                    $notification->setReceiver($post->getAuthor());
                    $notification->setPost($post);
                    $notification->setQuantity(1);
                    $notification->setSender($this->user());
                    $notificationRepo->persist($notification);
                }
            }
        }

        $em->flush();

        return $this->json([
            'response' => $response
        ]);
    }

    #[Route('/commentLike/{id}', name: 'comment_like')]
    #[Security('is_granted("ROLE_USER")')]
    public function commentLike(Comment $comment, LikeRepository $likeRepo, NotificationRepository $notificationRepo, EntityManagerInterface $em): Response
    {
        $like = $likeRepo->findOneBy(['user' => $this->user(), 'comment' => $comment]);

        if ($like) {
            $notification = $notificationRepo->findOneBy(['comment' => $comment, 'type' => 'comment_like', 'sender' => $this->user()]);
            if ($notification) {
                $notificationRepo->remove($notification);
            }

            $this->user()->removeLike($like);
            $response = ['status' => 'removed'];
        } else {
            $like = new Like();
            $like->setUser($this->user());
            $like->setComment($comment);
            $likeRepo->persist($like);
            $response = ['status' => 'added'];

            if ($comment->getAuthor() !== $this->user()) {
                $existNotify = $notificationRepo->findOneBy(['comment' => $comment, 'type' => 'comment_like']);

                if ($existNotify) {
                    if ($existNotify->getSeen()) {
                        $existNotify->setSeen(false);
                        $existNotify->setQuantity(1);
                    } elseif ($existNotify->getSender() !== $this->user() || $existNotify->getQuantity() > 1) {
                        $existNotify->setQuantity($existNotify->getQuantity() + 1);
                    }
                    $existNotify->setPublishedAt(new \DateTime('now'));
                    $existNotify->setSender($this->user());
                } else {
                    $notification = new Notification();
                    $notification->setType('comment_like');
                    $notification->setReceiver($comment->getAuthor());
                    $notification->setComment($comment);
                    if ($comment->getPost()) {
                        $notification->setPost($comment->getPost());
                    } else {
                        $notification->setSong($comment->getSong());
                    }
                    $notification->setQuantity(1);
                    $notification->setSender($this->user());
                    $notificationRepo->persist($notification);
                }
            }
        }

        $em->flush();

        return $this->json([
            'response' => $response
        ]);
    }
}
