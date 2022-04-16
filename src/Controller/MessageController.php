<?php

namespace App\Controller;

use App\CustomAbstracts\CustomAbstractController;
use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Security('is_granted("ROLE_USER")')]
class MessageController extends CustomAbstractController
{
    #[Route('/messages', name: 'message_conversations', methods: ['GET'])]
    public function index(UserRepository $userRepo): Response
    {
        $this->updateLastActivity();
        $conversations = $userRepo->findConversations($this->user()->getId());
        $users = [];

        foreach ($conversations as $conversation) {
            $users[] = $userRepo->findOneBy(['id' => $conversation['user']]);
        }

        $onlineUsers = $userRepo->findOnlineFollows(['user' => $this->user(), 'type' => 'following']);

        return $this->render('interface/message/conversations.html.twig', [
            'users' => $users,
            'onlineUsers' => $onlineUsers
        ]);
    }

    #[Route('/messages/{username}', name: 'message_conversation', methods: ['GET','POST'])]
    public function conversation(Request $request, User $user, MessageRepository $messageRepo): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class,$message);
        $form->handleRequest($request);

        foreach ($messageRepo->findBy(['receiver' => $this->user(), 'sender' => $user, 'seen' => false]) as $unseenMessage) {
            $unseenMessage->setSeen(true);
        }
        $messageRepo->flush();

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('imageFile')->getData() == null && $form->get('content')->getData() == null) {
                $this->addFlash('danger', $this->trans('flash.message.is.empty'));
            } else {
                $message->setSender($this->user());
                $message->setReceiver($user);
                $message->setSeen(false);
                $message->setSenderDeleted(false);
                $message->setReceiverDeleted(false);
                $message->setSentAt(new \DateTime('now'));

                if ($form->get('replyTo')->getData()) {
                    $message->setReplyTo($messageRepo->findOneBy(['id' => $form->get('replyTo')->getData()]));
                }

                if ($messageRepo->findConversationMessagesCount(['user_one' => $this->user(),'user_two' => $user])[0]['count'] > 200) {
                    foreach ($messageRepo->findConversation(['user_one' => $this->user(),'user_two' => $user],['sentAt' => 'DESC'],null,200) as $messageToDelete) {
                        $replyMessages = $messageRepo->findBy(['replyTo' => $messageToDelete]);
                        foreach ($replyMessages as $replyMessage) {
                            $replyMessage->setReplyTo(null);
                        }
                        $messageRepo->remove($messageToDelete);
                    }
                }

                $messageRepo->add($message);
            }

            return $this->redirectToRoute('message_conversation', [
                'username' => $user->getUsername()
            ]);
        }

        return $this->render('interface/message/conversation.html.twig', [
            'messages' => $messageRepo->findConversation(['user_one' => $this->user(),'user_two' => $user]),
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    #[Route('/conversation/{username}/deleteForMe', name: 'delete_conversation_for_me', methods: ['GET'])]
    public function deleteConversationForMe(User $user, MessageRepository $messageRepo): Response
    {
        $messages = $messageRepo->findConversation(['user_one' => $this->user(),'user_two' => $user]);

        foreach ($messages as $message) {
            if ($message->getSender() === $this->user()) {
                if ($message->getReceiverDeleted()) {
                    $messageRepo->remove($message);
                } else {
                    $message->setSenderDeleted(true);
                }
            } elseif ($message->getReceiver() === $this->user()) {
                if ($message->getSenderDeleted()) {
                    $messageRepo->remove($message);
                } else {
                    $message->setReceiverDeleted(true);
                }
            }
        }

        $messageRepo->flush();

        return $this->redirectToRoute('message_conversations');
    }

    #[Route('/conversation/{username}/deleteForEveryone', name: 'delete_conversation_for_everyone', methods: ['GET'])]
    public function deleteConversationForEveryone(User $user, MessageRepository $messageRepo): Response
    {
        $messages = $messageRepo->findConversation(['user_one' => $this->user(),'user_two' => $user]);

        foreach ($messages as $message) {
            $message->setReplyTo(null);
            $messageRepo->flush();
        }

        foreach ($messages as $message) {
            $messageRepo->remove($message);
        }

        return $this->redirectToRoute('message_conversations');
    }

    #[Route('/message/{id}/deleteForMe', name: 'delete_message_for_me', methods: ['GET'])]
    public function deleteMessageForMe(Message $message, MessageRepository $messageRepo): Response
    {
        if ($message->getSender() === $this->user()) {
            $username = $message->getReceiver()->getUsername();

            if ($message->getReceiverDeleted()) {
                $messageRepo->remove($message);
            } else {
                $message->setSenderDeleted(true);
            }
        } elseif ($message->getReceiver() === $this->user()) {
            $username = $message->getSender()->getUsername();

            if ($message->getSenderDeleted()) {
                $messageRepo->remove($message);
            } else {
                $message->setReceiverDeleted(true);
            }
        } else {
            return $this->redirectToRoute('message_conversations');
        }

        $messageRepo->flush();

        return $this->redirectToRoute('message_conversation', [
            'username' => $username
        ]);
    }

    #[Route('/message/{id}/deleteForEveryone', name: 'delete_message_for_everyone', methods: ['GET'])]
    public function deleteMessageForEveryone(Message $message, MessageRepository $messageRepo): Response
    {
        if ($message->getSender() === $this->user()) {
            $username = $message->getReceiver()->getUsername();
        } elseif ($message->getReceiver() === $this->user()) {
            $username = $message->getSender()->getUsername();
        } else {
            return $this->redirectToRoute('message_conversations');
        }

        $replyMessages = $messageRepo->findBy(['replyTo' => $message]);

        foreach ($replyMessages as $replyMessage) {
            $replyMessage->setReplyTo(null);
        }

        $messageRepo->remove($message);

        return $this->redirectToRoute('message_conversation', [
            'username' => $username
        ]);
    }
}
