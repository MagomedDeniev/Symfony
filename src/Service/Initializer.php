<?php

namespace App\Service;

use App\Entity\Action;
use App\Entity\Post;
use App\Entity\Person;
use App\Entity\Song;
use App\Entity\User;
use App\Entity\View;
use App\Repository\NotificationRepository;
use App\Repository\UserRepository;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Security\Core\Security;

class Initializer
{
    public function __construct(
        private NotificationRepository $notifications,
        private Security $security,
        private Defender $defender,
        private Compiler $compiler,
        private SlugifyInterface $slugify,
        private UserRepository $users,
        private EntityManagerInterface $em
    ){}

    public function initializeSongShow(Song $song)
    {
        if (!$this->defender->isGranted($this->getUser(), 'ROLE_GUEST')) {
            $view = $this->em->getRepository(View::class)->findOneBy(['user' => $this->getUser(), 'song' => $song]);
            if ($view) {
                $this->updateView($view);
            } else {
                $this->createView($song);
            }
        }

        $this->em->flush();
    }

    public function initializeSongNewAndEdit(Song $song, $form)
    {
        $song->setEditingDate(new \DateTime('now'));
        $this->setSearchData($song);
        $this->setSlug($song);

        if ($this->defender->isGranted($this->getUser(),'ROLE_SONG_MODERATOR') && $form->get('updateTags')->getData()) {
            $this->updateTagsDate($song);
        }
    }

    public function initializeSongNew(Song $song, $form = null)
    {
        $this->initializeSongNewAndEdit($song,$form);
        $song->setPublicationDate(new \DateTime('now'));

        if (!$song->getAuthor()) {
            $song->setAuthor($this->getUser());
        }

        $this->em->persist($song);
        $this->em->flush();
    }

    public function initializeSongEdit(Song $song, $form)
    {
        $this->initializeSongNewAndEdit($song,$form);

        if ($song->getStatus()) {
            $this->createAction($song, 'song_edited');
        }

        if ($this->defender->hasOnlyAuthorRightsInSongs($this->getUser()) && $form->get('sendForModeration')->getData()) {
            $song->setPublicationDate(new \DateTime('now'));
            $song->setStatus(0);
        }

        $this->em->flush();
    }

    public function initializePostNew(Post $post)
    {
        $this->generatePostSlug($post);

        $post->setDescription(mb_substr($this->compiler->htmlToText($post->getContent(),true), 0, 120));
        $post->setUpdatedAt(new \DateTime('now'));
        $post->setPublishedAt(new \DateTime('now'));
        $post->setAuthor($this->getUser());

        $this->em->persist($post);

        if ($this->defender->rightToSetTitleSlug($post) && $post->getImage()) {
            $this->em->flush();
        }
    }

    public function initializePostEdit(Post $post, $form)
    {
        $this->generatePostSlug($post);
        $post->setDescription(mb_substr($this->compiler->htmlToText($post->getContent(), true),0,120));

        if ($this->getUser() === $post->getAuthor()) {
            $this->disablePost($post);
        } elseif ($this->defender->isGranted($this->getUser(),'ROLE_POST_MODERATOR') && $form->get('moderation')->getData()) {
            $this->disablePost($post, true);
        }

        if ($post->getAuthor() !== $this->getUser()) {
            $this->createAction($post,'post_edited');
        }

        if ($this->defender->rightToSetTitleSlug($post) && $post->getImage()) {
            $this->em->flush();
        }
    }

    private function generatePostSlug(Post $post)
    {
        if ($post->getTitle()) {
            $post->setSlug($this->slugify->slugify($post->getTitle()));
        } else {
            $random = $this->getUser()->getId() * rand(1,147) . rand(784,1217) * rand(342,635) . chr(rand(97,122));
            $post->setSlug($this->slugify->slugify($random));
        }
    }

    private function disablePost(Post $post, $moderator = false) {
        /**
         * Post have always identical publishedAt & updatedAt dates
         * (When a user creates or edits before a moderator approves)
         * But updatedAt date changes, when a moderator approves or user edits after approval
         */
        if (!$moderator) {
            if ($post->getPublishedAt()->getTimestamp() === $post->getUpdatedAt()->getTimestamp()) {
                $post->setPublishedAt(new \DateTime('now'));
            }
            $post->setUpdatedAt(new \DateTime('now'));
        }

        $post->setStatus(null);

        foreach ($this->notifications->findPostNotifications($post) as $notification) {
            $this->em->remove($notification);
        }

        foreach ($post->getNotifications() as $notification) {
            $notification->setStatus(false);
        }
    }

    public function initializePersonNew(Person $person)
    {
        $person->setUpdatedAt(new \DateTime('now'));
        $this->setSlug($person);

        $this->em->persist($person);
        $this->em->flush();
    }

    public function initializeProfile(User $user): array
    {
        $userRepo = $this->users;

        try {
            $followers = $userRepo->followsCount(['user' => $user, 'type' => 'followers', 'accepted' => true]);
        } catch (NoResultException|NonUniqueResultException $e) {
            $followers = 0;
        }
        try {
            $following = $userRepo->followsCount(['user' => $user, 'type' => 'following', 'accepted' => true]);
        } catch (NoResultException|NonUniqueResultException $e) {
            $following = 0;
        }

        return [
            'followers' => $followers,
            'following' => $following
        ];
    }

    public function initializePersonEdit(Person $person)
    {
        $person->setUpdatedAt(new \DateTime('now'));
        $this->createAction($person,'person_edited');
        $this->setSlug($person);

        $this->em->flush();
    }

    private function createAction($entity, $type)
    {
        $action = new Action();
        $action->setType($type);
        $action->setModerator($this->getUser());

        if ($entity instanceof Song) {
            $action->setSong($entity);
        } elseif ($entity instanceof Person) {
            $action->setPerson($entity);
        } elseif ($entity instanceof Post) {
            $action->setPost($entity);
        }

        $this->em->persist($action);
    }

    private function createView(Song $song)
    {
        $view = new View();
        $view->setUser($this->getUser());
        $view->setSong($song);
        $view->setViewedAt(new \DateTime('now'));
        $view->setQuantity(1);

        $this->em->persist($view);
    }

    private function updateView(View $view)
    {
        $view->setQuantity($view->getQuantity() + 1);
        $view->setViewedAt(new \DateTime('now'));
    }

    private function updateTagsDate($entity)
    {
        if ($entity->getTags()) {
            foreach ($entity->getTags() as $tag) {
                $tag->setUpdatedAt(new \DateTime('now'));
            }
        }
    }

    private function setSearchData(Song $song)
    {
        $values = [
            $song->getFullTitle(),
            $song->getVocalist()->getFirstName() . ' ' . $song->getVocalist()->getLastName() . ' ' . $song->getTitle(),
            $this->compiler->htmlToText($song->getLyrics()),
        ];

        $song->setSearch(mb_strtolower(implode(' ', $values)));
    }

    private function setSlug($entity)
    {
        if ($entity instanceof Song) {
            $vocalist = '';
            if ($entity->getVocalist()){
                $vocalist = $entity->getVocalist()->getFullName() . ' ';
            }
            $entity->setSlug($this->slugify->slugify($vocalist . $entity->getTitle()));
        } elseif ($entity instanceof Person) {
            $entity->setSlug($this->slugify->slugify($entity->getFullName()));
        }
    }

    private function getUser()
    {
        if ($this->defender->isGranted($this->security->getUser(),'ROLE_GUEST')) {
            return $this->security->getUser();
        } else {
            return $this->users->findOneBy(['username' => $this->security->getUser()->getUsername()]);
        }
    }
}
