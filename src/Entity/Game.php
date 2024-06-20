<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    private ?int $id_game_api = null;

    /**
     * @var Collection<int, Subtype>
     */
    #[ORM\OneToMany(targetEntity: Subtype::class, mappedBy: 'Game')]
    private Collection $subtypes;

    public function __construct()
    {
        $this->subtypes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdGameApi(): ?int
    {
        return $this->id_game_api;
    }

    public function setIdGameApi(int $id_game_api): static
    {
        $this->id_game_api = $id_game_api;

        return $this;
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
            $subtype->setGame($this);
        }

        return $this;
    }

    public function removeSubtype(Subtype $subtype): static
    {
        if ($this->subtypes->removeElement($subtype)) {
            // set the owning side to null (unless already changed)
            if ($subtype->getGame() === $this) {
                $subtype->setGame(null);
            }
        }

        return $this;
    }
}
