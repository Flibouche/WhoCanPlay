<?php

namespace App\Entity;

use App\Repository\TopicRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TopicRepository::class)]
class Topic
{
    #region FIELDS
    // =======================================
    // =========== Region : FIELDS ===========
    // =======================================

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The title of the topic cannot be empty.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'The title of the topic must be at least {{ limit }} characters long.',
        maxMessage: 'The title of the topic cannot be longer than {{ limit }} characters.'
    )]
    #[Assert\Regex(
        pattern: '/^[\p{L}\p{N}\s.,!?\'"()-:]+$/u',
        message: 'The title can only contain letters, numbers, spaces, and certain punctuation.'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'The creation date of the topic cannot be empty.')]
    #[Assert\Type(
        type: '\DateTimeInterface',
        message: 'The value {{ value }} is not a valid {{ type }}.'
    )]
    private ?\DateTimeInterface $creationDate = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private ?bool $isLocked = false;

    #[ORM\ManyToOne(inversedBy: 'topics')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ORM\ManyToOne(inversedBy: 'topics')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 255)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Post>
     */
    #[ORM\OneToMany(targetEntity: Post::class, mappedBy: 'topic', orphanRemoval: true)]
    private Collection $posts;

    public function __construct()
    {
        $this->creationDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->posts = new ArrayCollection();
    }

    #endregion

    #region SIMPLE FIELDS
    // =======================================
    // ======== Region : SIMPLE FIELDS =======
    // =======================================

    // =================== ID ===================

    public function getId(): ?int
    {
        return $this->id;
    }

    // =================== Title ===================

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        $this->updateSlug();

        return $this;
    }

    // =================== Creation Date ===================

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): static
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    // =================== Locked ===================

    public function isLocked(): ?bool
    {
        return $this->isLocked;
    }

    public function setLocked(bool $isLocked): static
    {
        $this->isLocked = $isLocked;

        return $this;
    }

    // =================== Slug ===================

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function updateSlug(): void
    {
        $slugger = new AsciiSlugger();
        $this->slug = $slugger->slug($this->title ?? '')->lower();
    }

    #endregion

    #region COLLECTION(S)
    // =======================================
    // ======== Region : COLLECTION(S) =======
    // =======================================

    // =================== Game ===================

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;

        return $this;
    }

    // =================== User ===================

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    // =================== Posts ===================

    /**
     * @return Collection<int, Post>
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
            $post->setTopic($this);
        }

        return $this;
    }

    public function removePost(Post $post): static
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getTopic() === $this) {
                $post->setTopic(null);
            }
        }

        return $this;
    }

    #endregion

    #region MAGIC(S) METHOD(S)
    // =======================================
    // ===== Region : MAGIC(S) METHOD(S) =====
    // =======================================

    public function __toString(): String
    {
        return $this->title;
    }

    #endregion
}
