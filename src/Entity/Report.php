<?php

namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Report
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\OneToOne(targetEntity: Message::class, cascade: ['persist', 'remove'])]
    private $message;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist', 'remove'])]
    private $profile;

    #[ORM\OneToOne(inversedBy: 'report', targetEntity: Comment::class, cascade: ['persist', 'remove'])]
    private $comment;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $content;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'reports')]
    #[ORM\JoinColumn(nullable: false)]
    private $sender;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'accusations')]
    #[ORM\JoinColumn(nullable: false)]
    private $accused;

    #[ORM\Column(type: 'boolean')]
    private $seen;

    #[ORM\PrePersist]
    public function initialize()
    {
        $this->seen = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getProfile(): ?User
    {
        return $this->profile;
    }

    public function setProfile(?User $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function getComment(): ?Comment
    {
        return $this->comment;
    }

    public function setComment(?Comment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getAccused(): ?User
    {
        return $this->accused;
    }

    public function setAccused(?User $accused): self
    {
        $this->accused = $accused;

        return $this;
    }

    public function getSeen(): ?bool
    {
        return $this->seen;
    }

    public function setSeen(bool $seen): self
    {
        $this->seen = $seen;

        return $this;
    }
}
