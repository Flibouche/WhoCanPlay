<?php

namespace App\Entity;

use App\Repository\DisabilityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DisabilityRepository::class)]
class Disability
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Subtype>
     */
    #[ORM\OneToMany(targetEntity: Subtype::class, mappedBy: 'Disability')]
    private Collection $subtypes;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    public function __construct()
    {
        $this->subtypes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Subtype>
     */
    public function getSubtypes(): Collection
    {
        return $this->subtypes;
    }

    public function addSubtype(Subtype $subtype): static
    {
        if (!$this->subtypes->contains($subtype)) {
            $this->subtypes->add($subtype);
            $subtype->setDisability($this);
        }

        return $this;
    }

    public function removeSubtype(Subtype $subtype): static
    {
        if ($this->subtypes->removeElement($subtype)) {
            // set the owning side to null (unless already changed)
            if ($subtype->getDisability() === $this) {
                $subtype->setDisability(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function __toString(): String
    {
        return $this->name;
    }
}
