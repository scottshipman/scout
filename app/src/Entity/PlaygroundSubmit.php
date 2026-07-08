<?php

namespace App\Entity;

use App\Repository\PlaygroundSubmitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlaygroundSubmitRepository::class)]
#[ORM\Table(name: 'playground_submit')]
#[ORM\HasLifecycleCallbacks]
class PlaygroundSubmit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(name: 'favorite_food', length: 255)]
    private ?string $favoriteFood = null;

    #[ORM\Column(name: 'last_tv_show', length: 255)]
    private ?string $lastTvShow = null;

    #[ORM\Column(name: 'date_created')]
    private ?\DateTimeImmutable $dateCreated = null;

    #[ORM\Column(name: 'date_updated')]
    private ?\DateTimeImmutable $dateUpdated = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getFavoriteFood(): ?string
    {
        return $this->favoriteFood;
    }

    public function setFavoriteFood(string $favoriteFood): static
    {
        $this->favoriteFood = $favoriteFood;

        return $this;
    }

    public function getLastTvShow(): ?string
    {
        return $this->lastTvShow;
    }

    public function setLastTvShow(string $lastTvShow): static
    {
        $this->lastTvShow = $lastTvShow;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeImmutable
    {
        return $this->dateCreated;
    }

    public function getDateUpdated(): ?\DateTimeImmutable
    {
        return $this->dateUpdated;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        $this->dateCreated = $now;
        $this->dateUpdated = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->dateUpdated = new \DateTimeImmutable();
    }
}
