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

    /**
     * @var Collection<int, Topic>
     */
    #[ORM\OneToMany(targetEntity: Topic::class, mappedBy: 'game', orphanRemoval: true)]
    private Collection $topics;

    #[ORM\Column]
    private ?bool $status = false;

    public function __construct()
    {
        $this->subtypes = new ArrayCollection();
        $this->topics = new ArrayCollection();
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

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

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

    /**
     * @return Collection<int, Topic>
     */
    public function getTopics(): Collection
    {
        return $this->topics;
    }

    public function addTopic(Topic $topic): static
    {
        if (!$this->topics->contains($topic)) {
            $this->topics->add($topic);
            $topic->setGame($this);
        }

        return $this;
    }

    public function removeTopic(Topic $topic): static
    {
        if ($this->topics->removeElement($topic)) {
            // set the owning side to null (unless already changed)
            if ($topic->getGame() === $this) {
                $topic->setGame(null);
            }
        }

        return $this;
    }
}
