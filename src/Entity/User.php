<?php

namespace App\Entity;

use App\Repository\UserRepository;
use App\Validator\Constraints\BlockedEmail;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('username', message: 'form.username.already.exists')]
#[UniqueEntity('email', message: 'form.email.already.exists"')]
#[BlockedEmail('email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\NotBlank(message: 'username.not_blank')]
    #[Assert\Regex(
        pattern: '/^[a-z0-9._]+$/i',
        message: 'form.username.can.consist.symbols',
        htmlPattern: '^[a-zA-Z0-9._]+$'
    )]
    private $username;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $passwordRequestedAt;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\Email(message: 'form.its.not.an.email')]
    #[Assert\NotBlank(message: 'email.not_blank')]
    private $email;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $token;

    #[ORM\OneToOne(inversedBy: 'user', targetEntity: Profile::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $profile;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Song::class)]
    private $songs;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Comment::class, orphanRemoval: true)]
    private $comments;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PlaylistSong::class, orphanRemoval: true)]
    private $playlistSongs;

    #[ORM\OneToMany(mappedBy: 'replyTo', targetEntity: Comment::class, orphanRemoval: true)]
    private $replies;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Notification::class, orphanRemoval: true)]
    private $sentNotifications;

    #[ORM\OneToMany(mappedBy: 'receiver', targetEntity: Notification::class, orphanRemoval: true)]
    private $receivedNotifications;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Post::class, orphanRemoval: true)]
    private $posts;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Bookmark::class, orphanRemoval: true)]
    private $bookmarks;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $status;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $confirmedEmail;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'invitees')]
    private $invitedBy;

    #[ORM\OneToMany(mappedBy: 'invitedBy', targetEntity: User::class)]
    private $invitees;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $registeredAt;

    #[ORM\OneToMany(mappedBy: 'moderator', targetEntity: Action::class, orphanRemoval: true)]
    private $actions;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Action::class, orphanRemoval: true)]
    private $remarks;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: View::class, orphanRemoval: true)]
    private $views;

    #[ORM\OneToMany(mappedBy: 'follower', targetEntity: Follow::class, orphanRemoval: true)]
    private $following;

    #[ORM\OneToMany(mappedBy: 'followed', targetEntity: Follow::class, orphanRemoval: true)]
    private $followers;

    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'taggedUsers')]
    private $taggedPosts;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Like::class, orphanRemoval: true)]
    private $likes;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Message::class, orphanRemoval: true)]
    private $sentMessages;

    #[ORM\OneToMany(mappedBy: 'receiver', targetEntity: Message::class, orphanRemoval: true)]
    private $receivedMessages;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $lastActivityAt;

    #[ORM\Column(type: 'boolean')]
    private $hideOnline;

    #[ORM\Column(type: 'boolean')]
    private $closedAccount;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Report::class, orphanRemoval: true)]
    private $reports;

    #[ORM\OneToMany(mappedBy: 'accused', targetEntity: Report::class, orphanRemoval: true)]
    private $accusations;

    #[Pure] public function __construct()
    {
        $this->songs = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->playlistSongs = new ArrayCollection();
        $this->replies = new ArrayCollection();
        $this->sentNotifications = new ArrayCollection();
        $this->receivedNotifications = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->bookmarks = new ArrayCollection();
        $this->invitees = new ArrayCollection();
        $this->actions = new ArrayCollection();
        $this->remarks = new ArrayCollection();
        $this->views = new ArrayCollection();
        $this->following = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->taggedPosts = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->sentMessages = new ArrayCollection();
        $this->receivedMessages = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->accusations = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function initialize()
    {
        $this->closedAccount = false;
        $this->hideOnline = false;
        $this->registeredAt = new \DateTime('now');
        $this->roles = ["ROLE_USER"];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }
        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPasswordRequestedAt(): DateTime
    {
        return $this->passwordRequestedAt;
    }

    public function setPasswordRequestedAt($passwordRequestedAt): User
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken($token): User
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * {@inheritdoc}
     */
    public function getSalt(): ?string {return null;}

    /**
     * Removes sensitive data from the user.
     *
     * {@inheritdoc}
     */
    public function eraseCredentials(): void {}

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function getSongs(): Collection
    {
        return $this->songs;
    }

    public function addSong(Song $song): self
    {
        if (!$this->songs->contains($song)) {
            $this->songs[] = $song;
            $song->setAuthor($this);
        }

        return $this;
    }

    public function removeSong(Song $song): self
    {
        if ($this->songs->contains($song)) {
            $this->songs->removeElement($song);
            if ($song->getAuthor() === $this) {
                $song->setAuthor(null);
            }
        }

        return $this;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAuthor($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            if ($comment->getAuthor() === $this) {
                $comment->setAuthor(null);
            }
        }

        return $this;
    }

    public function getPlaylistSongs(): Collection
    {
        return $this->playlistSongs;
    }

    public function addPlaylistSong(PlaylistSong $playlistSong): self
    {
        if (!$this->playlistSongs->contains($playlistSong)) {
            $this->playlistSongs[] = $playlistSong;
            $playlistSong->setUser($this);
        }

        return $this;
    }

    public function removePlaylistSong(PlaylistSong $playlistSong): self
    {
        if ($this->playlistSongs->contains($playlistSong)) {
            $this->playlistSongs->removeElement($playlistSong);
            if ($playlistSong->getUser() === $this) {
                $playlistSong->setUser(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getReplies(): Collection
    {
        return $this->replies;
    }

    public function addReply(Comment $reply): self
    {
        if (!$this->replies->contains($reply)) {
            $this->replies[] = $reply;
            $reply->setReplyTo($this);
        }

        return $this;
    }

    public function removeReply(Comment $reply): self
    {
        if ($this->replies->contains($reply)) {
            $this->replies->removeElement($reply);
            if ($reply->getReplyTo() === $this) {
                $reply->setReplyTo(null);
            }
        }

        return $this;
    }

    public function getSentNotifications(): Collection
    {
        return $this->sentNotifications;
    }

    public function addSentNotification(Notification $sentNotification): self
    {
        if (!$this->sentNotifications->contains($sentNotification)) {
            $this->sentNotifications[] = $sentNotification;
            $sentNotification->setSender($this);
        }

        return $this;
    }

    public function removeSentNotification(Notification $sentNotification): self
    {
        if ($this->sentNotifications->contains($sentNotification)) {
            $this->sentNotifications->removeElement($sentNotification);
            if ($sentNotification->getSender() === $this) {
                $sentNotification->setSender(null);
            }
        }

        return $this;
    }

    public function getReceivedNotifications(): Collection
    {
        return $this->receivedNotifications;
    }

    public function addReceivedNotification(Notification $receivedNotification): self
    {
        if (!$this->receivedNotifications->contains($receivedNotification)) {
            $this->receivedNotifications[] = $receivedNotification;
            $receivedNotification->setReceiver($this);
        }

        return $this;
    }

    public function removeReceivedNotification(Notification $receivedNotification): self
    {
        if ($this->receivedNotifications->contains($receivedNotification)) {
            $this->receivedNotifications->removeElement($receivedNotification);
            if ($receivedNotification->getReceiver() === $this) {
                $receivedNotification->setReceiver(null);
            }
        }

        return $this;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

        return $this;
    }

    public function getBookmarks(): Collection
    {
        return $this->bookmarks;
    }

    public function addBookmark(Bookmark $bookmark): self
    {
        if (!$this->bookmarks->contains($bookmark)) {
            $this->bookmarks[] = $bookmark;
            $bookmark->setUser($this);
        }

        return $this;
    }

    public function removeBookmark(Bookmark $bookmark): self
    {
        if ($this->bookmarks->contains($bookmark)) {
            $this->bookmarks->removeElement($bookmark);
            if ($bookmark->getUser() === $this) {
                $bookmark->setUser(null);
            }
        }

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

    public function getConfirmedEmail(): ?string
    {
        return $this->confirmedEmail;
    }

    public function setConfirmedEmail(?string $confirmedEmail): self
    {
        $this->confirmedEmail = $confirmedEmail;

        return $this;
    }

    public function getInvitedBy(): ?self
    {
        return $this->invitedBy;
    }

    public function setInvitedBy(?self $invitedBy): self
    {
        $this->invitedBy = $invitedBy;

        return $this;
    }

    public function getInvitees(): Collection
    {
        return $this->invitees;
    }

    public function addInvitee(self $invitee): self
    {
        if (!$this->invitees->contains($invitee)) {
            $this->invitees[] = $invitee;
            $invitee->setInvitedBy($this);
        }

        return $this;
    }

    public function removeInvitee(self $invitee): self
    {
        if ($this->invitees->contains($invitee)) {
            $this->invitees->removeElement($invitee);
            if ($invitee->getInvitedBy() === $this) {
                $invitee->setInvitedBy(null);
            }
        }

        return $this;
    }

    public function getRegisteredAt(): ?\DateTimeInterface
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(\DateTimeInterface $registeredAt): self
    {
        $this->registeredAt = $registeredAt;

        return $this;
    }

    public function getActions(): Collection
    {
        return $this->actions;
    }

    public function addAction(Action $action): self
    {
        if (!$this->actions->contains($action)) {
            $this->actions[] = $action;
            $action->setModerator($this);
        }

        return $this;
    }

    public function removeAction(Action $action): self
    {
        if ($this->actions->contains($action)) {
            $this->actions->removeElement($action);
            if ($action->getModerator() === $this) {
                $action->setModerator(null);
            }
        }

        return $this;
    }

    public function getRemarks(): Collection
    {
        return $this->remarks;
    }

    public function addRemark(Action $remark): self
    {
        if (!$this->remarks->contains($remark)) {
            $this->remarks[] = $remark;
            $remark->setUser($this);
        }

        return $this;
    }

    public function removeRemark(Action $remark): self
    {
        if ($this->remarks->contains($remark)) {
            $this->remarks->removeElement($remark);
            if ($remark->getUser() === $this) {
                $remark->setUser(null);
            }
        }

        return $this;
    }

    public function getViews(): Collection
    {
        return $this->views;
    }

    public function addView(View $view): self
    {
        if (!$this->views->contains($view)) {
            $this->views[] = $view;
            $view->setUser($this);
        }

        return $this;
    }

    public function removeView(View $view): self
    {
        if ($this->views->contains($view)) {
            $this->views->removeElement($view);
            if ($view->getUser() === $this) {
                $view->setUser(null);
            }
        }

        return $this;
    }

    public function getFollowers(): Collection
    {
        return $this->followers;
    }

    public function getFollowing(): Collection
    {
        return $this->following;
    }

    public function removeFollow(Follow $follow): self
    {
        if ($this->following->contains($follow)) {
            $this->following->removeElement($follow);
        }

        return $this;
    }

    public function addFollow($followed): Follow
    {
        $follow = new Follow();
        $follow->setFollower($this);
        $follow->setFollowed($followed);

        return $follow;
    }

    public function getTaggedPosts(): Collection
    {
        return $this->taggedPosts;
    }

    public function addTaggedPost(Post $taggedPost): self
    {
        if (!$this->taggedPosts->contains($taggedPost)) {
            $this->taggedPosts[] = $taggedPost;
            $taggedPost->addUser($this);
        }

        return $this;
    }

    public function removeTaggedPost(Post $taggedPost): self
    {
        if ($this->taggedPosts->contains($taggedPost)) {
            $this->taggedPosts->removeElement($taggedPost);
            $taggedPost->removeUser($this);
        }

        return $this;
    }

    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
            $like->setUser($this);
        }

        return $this;
    }

    public function removeLike(Like $like): self
    {
        if ($this->likes->contains($like)) {
            $this->likes->removeElement($like);
            if ($like->getUser() === $this) {
                $like->setUser(null);
            }
        }

        return $this;
    }

    public function getSentMessages(): Collection
    {
        return $this->sentMessages;
    }

    public function addSentMessage(Message $sentMessage): self
    {
        if (!$this->sentMessages->contains($sentMessage)) {
            $this->sentMessages[] = $sentMessage;
            $sentMessage->setSender($this);
        }

        return $this;
    }

    public function removeSentMessage(Message $sentMessage): self
    {
        if ($this->sentMessages->contains($sentMessage)) {
            $this->sentMessages->removeElement($sentMessage);
            if ($sentMessage->getSender() === $this) {
                $sentMessage->setSender(null);
            }
        }

        return $this;
    }

    public function getReceivedMessages(): Collection
    {
        return $this->receivedMessages;
    }

    public function addReceivedMessage(Message $receivedMessage): self
    {
        if (!$this->receivedMessages->contains($receivedMessage)) {
            $this->receivedMessages[] = $receivedMessage;
            $receivedMessage->setReceiver($this);
        }

        return $this;
    }

    public function removeReceivedMessage(Message $receivedMessage): self
    {
        if ($this->receivedMessages->contains($receivedMessage)) {
            $this->receivedMessages->removeElement($receivedMessage);
            if ($receivedMessage->getReceiver() === $this) {
                $receivedMessage->setReceiver(null);
            }
        }

        return $this;
    }

    public function getLastActivityAt(): ?\DateTimeInterface
    {
        return $this->lastActivityAt;
    }

    public function setLastActivityAt(?\DateTimeInterface $lastActivityAt): self
    {
        $this->lastActivityAt = $lastActivityAt;

        return $this;
    }

    public function getHideOnline(): ?bool
    {
        return $this->hideOnline;
    }

    public function setHideOnline(bool $hideOnline): self
    {
        $this->hideOnline = $hideOnline;

        return $this;
    }

    public function getClosedAccount(): ?bool
    {
        return $this->closedAccount;
    }

    public function setClosedAccount(bool $closedAccount): self
    {
        $this->closedAccount = $closedAccount;

        return $this;
    }

    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports[] = $report;
            $report->setSender($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->reports->contains($report)) {
            $this->reports->removeElement($report);
            if ($report->getSender() === $this) {
                $report->setSender(null);
            }
        }

        return $this;
    }

    public function getAccusations(): Collection
    {
        return $this->accusations;
    }

    public function addAccusation(Report $accusation): self
    {
        if (!$this->accusations->contains($accusation)) {
            $this->accusations[] = $accusation;
            $accusation->setSender($this);
        }

        return $this;
    }

    public function removeAccusation(Report $accusation): self
    {
        if ($this->accusations->contains($accusation)) {
            $this->accusations->removeElement($accusation);
            if ($accusation->getSender() === $this) {
                $accusation->setSender(null);
            }
        }

        return $this;
    }
}
