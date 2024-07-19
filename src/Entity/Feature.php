<?php

namespace App\Entity;

use App\Enum\FeatureState;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\FeatureRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[ORM\Entity(repositoryClass: FeatureRepository::class)]
class Feature
{
    #region FIELDS
    // =======================================
    // =========== Region : FIELDS ===========
    // =======================================

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'features')]
    private ?Disability $disability = null;

    #[ORM\ManyToOne(inversedBy: 'features')]
    private ?Game $game = null;

    #[ORM\ManyToOne(inversedBy: 'features')]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?int $id_game_api = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: 'string', enumType: FeatureState::class)]
    private FeatureState $state = FeatureState::NOT_OPENED;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $submissionDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, Image>
     */
    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'feature', cascade: ['persist'])]
    private Collection $images;

    #endregion

    #region CONSTRUCT
    // =======================================
    // ========== Region : CONSTRUCT =========
    // =======================================

    public function __construct()
    {
        $this->submissionDate = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->images = new ArrayCollection();
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

    // =================== Disability ===================

    public function getDisability(): ?Disability
    {
        return $this->disability;
    }

    public function setDisability(?Disability $disability): static
    {
        $this->disability = $disability;

        return $this;
    }

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

    // =================== ID Game API ===================

    public function getIdGameApi(): ?int
    {
        return $this->id_game_api;
    }

    public function setIdGameApi(?int $id_game_api): static
    {
        $this->id_game_api = $id_game_api;

        return $this;
    }

    // =================== Name ===================

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        $this->updateSlug();

        return $this;
    }

    // =================== Content ===================

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    // =================== State ===================

    public function getState(): FeatureState
    {
        return $this->state;
    }

    public function setState(FeatureState $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getStateAsString(): string
    {
        return $this->state->value;
    }

    // =================== Submission Date ===================

    public function getSubmissionDate(): ?\DateTimeInterface
    {
        return $this->submissionDate;
    }

    public function setSubmissionDate(\DateTimeInterface $submissionDate): static
    {
        $this->submissionDate = $submissionDate;

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
        $this->slug = $slugger->slug($this->name ?? '')->lower();
    }

    // =================== Updated At ===================

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #endregion

    #region COLLECTION(S)
    // =======================================
    // ======== Region : COLLECTION(S) =======
    // =======================================

    // =================== Images ===================

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setFeature($this);
        }

        return $this;
    }

    public function removeImage(Image $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getFeature() === $this) {
                $image->setFeature(null);
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
        return $this->name;
    }

    #endregion
}
