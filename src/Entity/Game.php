<?php

namespace App\Entity;

use App\Repository\GameRepository;
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
}
