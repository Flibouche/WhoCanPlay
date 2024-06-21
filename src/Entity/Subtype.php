<?php

namespace App\Entity;

use App\Enum\SubtypeState;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\SubtypeRepository;

#[ORM\Entity(repositoryClass: SubtypeRepository::class)]
class Subtype
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'subtypes')]
    private ?Disability $Disability = null;

    #[ORM\ManyToOne(inversedBy: 'subtypes')]
    private ?Game $Game = null;

    #[ORM\ManyToOne(inversedBy: 'subtypes')]
    private ?User $User = null;

    #[ORM\Column(nullable: true)]
    private ?int $id_game_api = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(type: 'string', enumType: SubtypeState::class)]
    private SubtypeState $state;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDisability(): ?Disability
    {
        return $this->Disability;
    }

    public function setDisability(?Disability $Disability): static
    {
        $this->Disability = $Disability;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->Game;
    }

    public function setGame(?Game $Game): static
    {
        $this->Game = $Game;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }

    public function getIdGameApi(): ?int
    {
        return $this->id_game_api;
    }

    public function setIdGameApi(?int $id_game_api): static
    {
        $this->id_game_api = $id_game_api;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getState(): SubtypeState
    {
        return $this->state;
    }

    public function setState(SubtypeState $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function __toString(): String
    {
        return $this->name;
    }
}
