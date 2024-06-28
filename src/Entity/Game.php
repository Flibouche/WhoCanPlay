<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #region FIELDS
    // =======================================
    // =========== Region : FIELDS ===========
    // =======================================

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    private ?int $id_game_api = null;

    /**
     * @var Collection<int, Feature>
     */
    #[ORM\OneToMany(targetEntity: Feature::class, mappedBy: 'game')]
    private Collection $features;

    /**
     * @var Collection<int, Topic>
     */
    #[ORM\OneToMany(targetEntity: Topic::class, mappedBy: 'game', orphanRemoval: true)]
    private Collection $topics;

    #[ORM\Column]
    private ?bool $status = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #endregion

    #region CONSTRUCT
    // =======================================
    // ========== Region : CONSTRUCT =========
    // =======================================

    public function __construct()
    {
        $this->features = new ArrayCollection();
        $this->topics = new ArrayCollection();
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

    // =================== ID Game API ===================

    public function getIdGameApi(): ?int
    {
        return $this->id_game_api;
    }

    public function setIdGameApi(int $id_game_api): static
    {
        $this->id_game_api = $id_game_api;

        return $this;
    }

    // =================== Status ===================

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

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
            $feature->setGame($this);
        }

        return $this;
    }

    public function removeFeature(Feature $feature): static
    {
        if ($this->features->removeElement($feature)) {
            // set the owning side to null (unless already changed)
            if ($feature->getGame() === $this) {
                $feature->setGame(null);
            }
        }

        return $this;
    }

    // =================== Topics ===================

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

    #endregion
}