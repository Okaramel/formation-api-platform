<?php

namespace App\Entity;

use App\Repository\OverAccountRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OverAccountRepository::class)]
class OverAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $namecard = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    private array $endorsement = [];

    #[ORM\Column]
    private array $competitive = [];

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $last_updated_at = null;

    #[ORM\OneToOne(inversedBy: 'overAccount', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getNamecard(): ?string
    {
        return $this->namecard;
    }

    public function setNamecard(?string $namecard): static
    {
        $this->namecard = $namecard;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getEndorsement(): array
    {
        return $this->endorsement;
    }

    public function setEndorsement(array $endorsement): static
    {
        $this->endorsement = $endorsement;

        return $this;
    }

    public function getCompetitive(): array
    {
        return $this->competitive;
    }

    public function setCompetitive(array $competitive): static
    {
        $this->competitive = $competitive;

        return $this;
    }

    public function getLastUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->last_updated_at;
    }

    public function setLastUpdatedAt(?\DateTimeImmutable $last_updated_at): static
    {
        $this->last_updated_at = $last_updated_at;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    
}
