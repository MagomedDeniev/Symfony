<?php

namespace App\Entity;

use App\Repository\PeopleRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Validator\Constraints as MyAssert;
use Doctrine\Common\Collections\Collection;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PeopleRepository::class)]
#[MyAssert\UniquePeople]
#[Vich\Uploadable]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    private $firstName;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Type('string')]
    private $lastName;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\Date]
    private $birthDay;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Type('string')]
    private $biography;

    #[Vich\UploadableField(mapping: 'people_images', fileNameProperty: 'picture')]
    #[Assert\File(mimeTypes: ['image/jpeg','image/png','image/gif'], mimeTypesMessage: 'image.have.to.be.jpg.or.png')]
    private $pictureFile;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Type('string')]
    private $picture;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Assert\Type(\DateTimeInterface::class)]
    private $updatedAt;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Type('string')]
    private $slug;

    #[ORM\OneToMany(mappedBy: 'vocalist', targetEntity: Song::class, orphanRemoval: true)]
    private $songs;

    #[ORM\ManyToMany(targetEntity: Song::class, mappedBy: 'featuring')]
    private $featuring;

    #[ORM\ManyToMany(targetEntity: Activity::class, inversedBy: 'people')]
    private $activity;

    #[ORM\OneToMany(mappedBy: 'person', targetEntity: Action::class, orphanRemoval: true)]
    private $actions;

    #[Pure] public function __construct()
    {
        $this->songs = new ArrayCollection();
        $this->activity = new ArrayCollection();
        $this->featuring = new ArrayCollection();
        $this->actions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setPictureFile(?File $pictureFile = null): void
    {
        $this->pictureFile = $pictureFile;

        if (null !== $pictureFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getPictureFile(): ?File
    {
        return $this->pictureFile;
    }

    public function getFullName(): string
    {
        $space = ' ';
        if (empty($this->lastName)) {
            $space = '';
        }
        return $this->firstName . $space . $this->lastName;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getBirthDay(): ?\DateTimeInterface
    {
        return $this->birthDay;
    }

    public function setBirthDay(?\DateTimeInterface $birthDay): self
    {
        $this->birthDay = $birthDay;

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): self
    {
        $this->biography = $biography;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): self
    {
        $this->picture = $picture;

        return $this;
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

    /**
     * @return Collection|Song[]
     */
    public function getSongs(): Collection
    {
        return $this->songs;
    }

    public function addSong(Song $song): self
    {
        if (!$this->songs->contains($song)) {
            $this->songs[] = $song;
            $song->setVocalist($this);
        }

        return $this;
    }

    public function removeSong(Song $song): self
    {
        if ($this->songs->contains($song)) {
            $this->songs->removeElement($song);
            if ($song->getVocalist() === $this) {
                $song->setVocalist(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Activity[]
     */
    public function getActivity(): Collection
    {
        return $this->activity;
    }

    public function addActivity(Activity $activity): self
    {
        if (!$this->activity->contains($activity)) {
            $this->activity[] = $activity;
        }

        return $this;
    }

    public function removeActivity(Activity $activity): self
    {
        if ($this->activity->contains($activity)) {
            $this->activity->removeElement($activity);
        }

        return $this;
    }

    /**
     * @return Collection|Song[]
     */
    public function getFeaturing(): Collection
    {
        return $this->featuring;
    }

    public function addFeaturing(Song $featuring): self
    {
        if (!$this->featuring->contains($featuring)) {
            $this->featuring[] = $featuring;
            $featuring->addFeaturing($this);
        }

        return $this;
    }

    public function removeFeaturing(Song $featuring): self
    {
        if ($this->featuring->contains($featuring)) {
            $this->featuring->removeElement($featuring);
            $featuring->removeFeaturing($this);
        }

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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
            $action->setPerson($this);
        }

        return $this;
    }

    public function removeAction(Action $action): self
    {
        if ($this->actions->contains($action)) {
            $this->actions->removeElement($action);
            if ($action->getPerson() === $this) {
                $action->setPerson(null);
            }
        }

        return $this;
    }
}
