<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\DisabilityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\String\Slugger\AsciiSlugger;

#[ORM\Entity(repositoryClass: DisabilityRepository::class)]
class Disability
{
    // =======================================
    // ========== Section : FIELDS ===========
    // =======================================
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Feature>
     */
    #[ORM\OneToMany(targetEntity: Feature::class, mappedBy: 'Disability')]
    private Collection $features;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $icon = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    /**
     * @var Collection<int, Image>
     */
    #[ORM\OneToMany(targetEntity: Image::class, mappedBy: 'Disability')]
    private Collection $images;

    // =======================================
    // ========= Section : CONSTRUCT =========
    // =======================================

    public function __construct()
    {
        $this->features = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    // =======================================
    // ===== Section : GETTERS & SETTERS =====
    // =======================================

    // ******************* ID *******************
    
    public function getId(): ?int
    {
        return $this->id;
    }

    // ******************* Name *******************
    
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

    // ******************* Icon *******************
    
    public function getIcon(): ?string
    {
        return $this->icon;
    }
    
    public function setIcon(string $icon): static
    {
        $this->icon = $icon;
        
        return $this;
    }

    // ******************* Slug *******************

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

    // ****************************************************
    // ******************* Colletion(s) *******************
    // ****************************************************

    // ******************* Features *******************

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

    // ******************* Images *******************

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
            $image->setDisability($this);
        }

        return $this;
    }

    public function removeImage(Image $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getDisability() === $this) {
                $image->setDisability(null);
            }
        }

        return $this;
    }

    // =======================================
    // ==== Section : MAGIC(S) METHOD(S) =====
    // =======================================

    public function __toString(): String
    {
        return $this->name;
    }
}
