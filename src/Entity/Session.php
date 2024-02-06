<?php

namespace App\Entity;

use App\Repository\SessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SessionRepository::class)]
class Session
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $heure = null;

    #[ORM\ManyToOne(inversedBy: 'sessions')]
    private ?User $pro = null;

    #[ORM\ManyToOne]
    private ?User $eleve = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHeure(): ?\DateTimeInterface
    {
        return $this->heure;
    }

    public function setHeure(\DateTimeInterface $heure): static
    {
        $this->heure = $heure;

        return $this;
    }

    public function getPro(): ?User
    {
        return $this->pro;
    }

    public function setPro(?User $pro): static
    {
        $this->pro = $pro;

        return $this;
    }

    public function getEleve(): ?User
    {
        return $this->eleve;
    }

    public function setEleve(?User $eleve): static
    {
        $this->eleve = $eleve;

        return $this;
    }
}
