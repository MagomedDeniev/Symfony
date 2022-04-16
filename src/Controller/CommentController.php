<?php

namespace App\Controller;

use App\CustomAbstracts\CustomAbstractController;
use App\Entity\Action;
use App\Entity\Comment;
use App\Entity\Notification;
use App\Form\CommentType;
use App\Repository\ActionRepository;
use App\Repository\CommentRepository;
use App\Repository\NotificationRepository;
use App\Repository\PostRepository;
use App\Repository\SongRepository;
use App\Repository\UserRepository;
use App\Service\Defender;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Security('is_granted("ROLE_USER")')]
class CommentController extends CustomAbstractController
{
    #[Route('/comment/new/{type}/{id}', name: 'comment_new', methods: ['POST'])]
    public function newComment(Request $request, $type, $id, UserRepository $userRepo, NotificationRepository $notificationRepository, CommentRepository $commentRepository, SongRepository $songRepository, PostRepository $postRepository): Response
    {
        if ($type == 'song') {
            $entity = $songRepository->findOneBy(['id' => $id]);
        } else {
            $entity = $postRepository->findOneBy(['id' => $id]);
        }

        $comment = new Comment();
        $comment->setAuthor($this->user());
        $entity->addComment($comment);

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('replyTo')->getData()) {
                $receiver = $userRepo->findOneBy(['username' => $form->get('replyTo')->getData()]);
                $replyingComment = $commentRepository->findOneBy(['id' => $form->get('replyFor')->getData()]);
                $existNotify = $notificationRepository->findOneBy(['type' => 'comment_reply', $type => $entity, 'receiver' => $receiver]);

                if ($existNotify) {
                    if ($existNotify->getSeen()) {
                        $existNotify->setSeen(false);
                        $existNotify->setQuantity(1);
                    } elseif ($existNotify->getSender() !== $this->user() || $existNotify->getQuantity() > 1) {
                        $existNotify->setQuantity($existNotify->getQuantity() + 1);
                    }
                    $existNotify->setPublishedAt(new \DateTime('now'));
                    $existNotify->setComment($comment);
                    $existNotify->setSender($this->user());
                } else {
                    $notification = new Notification();
                    $notification->setType('comment_reply');
                    $notification->setReceiver($receiver);
                    $notification->setComment($comment);
                    $notification->setQuantity(1);
                    $notification->setSender($this->user());

                    if ($type == 'song') {
                        $notification->setSong($entity);
                    } else {
                        $notification->setPost($entity);
                    }
                    $notificationRepository->persist($notification);
                }

                $comment->setReplyTo($receiver);

                if ($replyingComment->getParent()) {
                    $comment->setParent($replyingComment->getParent());
                } else {
                    $comment->setParent($replyingComment);
                }
            }

            $existNotify = $notificationRepository->findOneBy(['type' => 'post_comment', 'post' => $comment->getPost()]);

            if ($type == 'post' && $existNotify && $this->user() !== $comment->getPost()->getAuthor() && $form->get('replyTo')->getData() !== $comment->getPost()->getAuthor()->getUsername()) {
                if ($existNotify->getSeen()) {
                    $existNotify->setSeen(false);
                    $existNotify->setQuantity(1);
                } elseif ($existNotify->getSender() !== $comment->getAuthor() || $existNotify->getQuantity() > 1) {
                    $existNotify->setQuantity($existNotify->getQuantity() + 1);
                }
                $existNotify->setPublishedAt(new \DateTime('now'));
                $existNotify->setComment($comment);
                $existNotify->setSender($this->user());
            } elseif ($type == 'post' && $this->user() !== $comment->getPost()->getAuthor() && $form->get('replyTo')->getData() !== $comment->getPost()->getAuthor()->getUsername()) {
                $notify = new Notification();
                $notify->setReceiver($comment->getPost()->getAuthor());
                $notify->setComment($comment);
                $notify->setQuantity(1);
                $notify->setPost($comment->getPost());
                $notify->setSender($this->user());
                $notify->setType('post_comment');
                $notificationRepository->persist($notify);
            }

            $commentRepository->add($comment);

            $this->addFlash('success', $this->trans('flash.comment.added'));

            if ($type == 'song') {
                return $this->redirectToRoute('song_show', [
                    'slug' => $entity->getSlug()
                ]);
            } else {
                return $this->redirectToRoute('post_show', [
                    'id' => $entity->getId()
                ]);
            }
        }

        $this->addFlash('danger', $this->trans('flash.comment.adding.error'));

        if ($type == 'song') {
            return $this->redirectToRoute('song_show', [
                'slug' => $entity->getSlug()
            ]);
        } else {
            return $this->redirectToRoute('post_show', [
                'id' => $entity->getId(),
            ]);
        }
    }

    #[Route('/comment/delete/{id}', name: 'delete_comment', methods: ['POST'])]
    public function deleteComment(Request $request, Comment $comment, Defender $defender, ActionRepository $actionRepository, CommentRepository $commentRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token')) && $defender->rightToDeleteComment($this->user(),$comment)) {

            if ($comment->getPost() && $comment->getPost()->getAuthor() !== $this->user() && $comment->getAuthor() !== $this->user() || $comment->getSong() && $comment->getSong()->getAuthor() !== $this->user() && $comment->getAuthor() !== $this->user()) {
                $action = new Action();
                $action->setModerator($this->user());
                $action->setContent($comment->getMessage());
                $action->setType('comment_deleted');
                $action->setUser($comment->getAuthor());

                if ($comment->getSong()) {
                    $action->setSong($comment->getSong());
                } elseif ($comment->getPost()) {
                    $action->setPost($comment->getPost());
                }

                $actionRepository->persist($action);
            }

            $commentRepository->remove($comment);

            $this->addFlash('success', $this->trans('flash.comment.deleted'));
        } else {
            $this->addFlash('danger', $this->trans('flash.comment.deleting.error'));
        }

        if ($comment->getSong()) {
            return $this->redirectToRoute('song_show', [
                'slug' => $comment->getSong()->getSlug()
            ]);
        } else {
            return $this->redirectToRoute('post_show', [
                'id' => $comment->getPost()->getId()
            ]);
        }
    }
}
