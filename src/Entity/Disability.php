<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DisabilityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DisabilityRepository::class)]
class Disability
{
    #region FIELDS
    // =======================================
    // =========== Region : FIELDS ===========
    // =======================================

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'The name of the disability cannot be empty.')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'The name of the disability must be at least {{ limit }} characters long.',
        maxMessage: 'The name of the disability cannot be longer than {{ limit }} characters.'
    )]
    #[Assert\Regex(
        pattern: '/^[\p{L}\p{N}\s.,!?\'"()-:]+$/u',
        message: 'The name can only contain letters, numbers, spaces, and certain punctuation.'
    )]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'The icon of the disability cannot be empty.')]
    #[Assert\Length(max: 255)]
    private ?string $icon = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Feature>
     */
    #[ORM\OneToMany(targetEntity: Feature::class, mappedBy: 'disability')]
    private Collection $features;

    #endregion

    #region CONSTRUCT
    // =======================================
    // ========== Region : CONSTRUCT =========
    // =======================================

    // =================== CONSTRUCT ===================

    public function __construct()
    {
        $this->features = new ArrayCollection();
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

    // =================== Icon ===================

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): static
    {
        $this->icon = $icon;

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
    #endregion

    #region COLLECTION(S)
    // =======================================
    // ======== Region : COLLECTION(S) =======
    // =======================================

    // =================== Features ===================

    /**
     * @return Collection<int, Feature>
     */
    public function getFeatures(): Collection
    {
        return $this->features;
    }

    public function addFeature(Feature $feature): static
    {
        if (!$this->features->contains($feature)) {
            $this->features->add($feature);
            $feature->setDisability($this);
        }

        return $this;
    }

    public function removeFeature(Feature $feature): static
    {
        if ($this->features->removeElement($feature)) {
            // set the owning side to null (unless already changed)
            if ($feature->getDisability() === $this) {
                $feature->setDisability(null);
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