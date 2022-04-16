<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\SongRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Validator\Constraints as MyAssert;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: SongRepository::class)]
#[MyAssert\UniqueSong]
#[Vich\Uploadable]
class Song
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    private $title;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Type('string')]
    private $slug;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Type('string')]
    private $lyrics;

    #[Vich\UploadableField(mapping: 'vocalist_songs', fileNameProperty: 'audio')]
    #[Assert\File(mimeTypes: ['audio/mpeg','audio/mp4','audio/vnd.wav'], mimeTypesMessage: 'audio.have.to.be.mpeg.or.wav')]
    private $audioFile;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $audio;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\Date]
    private $releaseDate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Assert\Type(\DateTimeInterface::class)]
    private $publicationDate;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Assert\Type(\DateTimeInterface::class)]
    private $editingDate;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Assert\Type('bool')]
    private $featured;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Assert\Type('bool')]
    private $status;

    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'songs')]
    private $vocalist;

    #[ORM\ManyToMany(targetEntity: Person::class, inversedBy: 'featuring')]
    private $featuring;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'songs')]
    private $author;

    #[ORM\OneToMany(mappedBy: 'song', targetEntity: Comment::class, orphanRemoval: true)]
    private $comments;

    #[ORM\OneToMany(mappedBy: 'song', targetEntity: PlaylistSong::class, orphanRemoval: true)]
    private $playlistSongs;

    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'songs')]
    private $posts;

    #[ORM\OneToMany(mappedBy: 'song', targetEntity: Notification::class)]
    private $notifications;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'songs')]
    private $tags;

    #[ORM\Column(type: 'text', nullable: true)]
    private $translation;

    #[ORM\OneToMany(mappedBy: 'song', targetEntity: Action::class, orphanRemoval: true)]
    private $actions;

    #[ORM\OneToMany(mappedBy: 'song', targetEntity: View::class, orphanRemoval: true)]
    private $views;

    #[ORM\Column(type: 'text', nullable: true)]
    private $search;

    #[ORM\OneToMany(mappedBy: 'song', targetEntity: Message::class)]
    private $messages;

    #[Pure] public function __construct()
    {
        $this->featuring = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->playlistSongs = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->views = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function setAudioFile(?File $audioFile = null): void
    {
        $this->audioFile = $audioFile;

        if (null !== $audioFile) {
            $this->editingDate = new \DateTimeImmutable();
        }
    }

    public function getAudioFile(): ?File
    {
        return $this->audioFile;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getFullTitle(): ?string
    {
        ($this->vocalist) ? $fullName = $this->vocalist->getFullName() . ' - ' : $fullName = '';

        return $fullName . $this->getTitle();
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getLyrics(): ?string
    {
        return $this->lyrics;
    }

    public function setLyrics(?string $lyrics): self
    {
        $this->lyrics = $lyrics;

        return $this;
    }

    public function getAudio(): ?string
    {
        return $this->audio;
    }

    public function setAudio(?string $audio): self
    {
        $this->audio = $audio;

        return $this;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->releaseDate;
    }

    public function setReleaseDate(?\DateTimeInterface $releaseDate): self
    {
        $this->releaseDate = $releaseDate;

        return $this;
    }

    public function getPublicationDate(): ?\DateTimeInterface
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(?\DateTimeInterface $publicationDate): self
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    public function getEditingDate(): ?\DateTimeInterface
    {
        return $this->editingDate;
    }

    public function setEditingDate(?\DateTimeInterface $editingDate): self
    {
        $this->editingDate = $editingDate;

        return $this;
    }

    public function getFeatured(): ?bool
    {
        return $this->featured;
    }

    public function setFeatured(?bool $featured): self
    {
        $this->featured = $featured;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getVocalist(): ?Person
    {
        return $this->vocalist;
    }

    public function setVocalist(?Person $vocalist): self
    {
        $this->vocalist = $vocalist;

        return $this;
    }

    /**
     * @return Collection|Person[]
     */
    public function getFeaturing(): Collection
    {
        return $this->featuring;
    }

    public function addFeaturing(Person $featuring): self
    {
        if (!$this->featuring->contains($featuring)) {
            $this->featuring[] = $featuring;
        }

        return $this;
    }

    public function removeFeaturing(Person $featuring): self
    {
        if ($this->featuring->contains($featuring)) {
            $this->featuring->removeElement($featuring);
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setSong($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            if ($comment->getSong() === $this) {
                $comment->setSong(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PlaylistSong[]
     */
    public function getPlaylistSongs(): Collection
    {
        return $this->playlistSongs;
    }

    public function addPlaylistSong(PlaylistSong $playlistSong): self
    {
        if (!$this->playlistSongs->contains($playlistSong)) {
            $this->playlistSongs[] = $playlistSong;
            $playlistSong->setSong($this);
        }

        return $this;
    }

    public function removePlaylistSong(PlaylistSong $playlistSong): self
    {
        if ($this->playlistSongs->contains($playlistSong)) {
            $this->playlistSongs->removeElement($playlistSong);
            if ($playlistSong->getSong() === $this) {
                $playlistSong->setSong(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->addSong($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            $post->removeSong($this);
        }

        return $this;
    }

    /**
     * @return Collection|Notification[]
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): self
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications[] = $notification;
            $notification->setSong($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): self
    {
        if ($this->notifications->contains($notification)) {
            $this->notifications->removeElement($notification);
            if ($notification->getSong() === $this) {
                $notification->setSong(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
        }

        return $this;
    }

    public function getTranslation(): ?string
    {
        return $this->translation;
    }

    public function setTranslation(?string $translation): self
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * @return Collection|Action[]
     */
    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addAction(Action $action): self
    {
        if (!$this->actions->contains($action)) {
            $this->actions[] = $action;
            $action->setSong($this);
        }

        return $this;
    }

    public function removeAction(Action $action): self
    {
        if ($this->actions->contains($action)) {
            $this->actions->removeElement($action);
            if ($action->getSong() === $this) {
                $action->setSong(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|View[]
     */
    public function getViews(): Collection
    {
        return $this->views;
    }

    public function addView(View $view): self
    {
        if (!$this->views->contains($view)) {
            $this->views[] = $view;
            $view->setSong($this);
        }

        return $this;
    }

    public function removeView(View $view): self
    {
        if ($this->views->contains($view)) {
            $this->views->removeElement($view);
            if ($view->getSong() === $this) {
                $view->setSong(null);
            }
        }

        return $this;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function setSearch(?string $search): self
    {
        $this->search = $search;

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setSong($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            if ($message->getSong() === $this) {
                $message->setSong(null);
            }
        }

        return $this;
    }
}
