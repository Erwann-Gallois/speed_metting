<?php

namespace App\Entity;

use App\Repository\VariableRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VariableRepository::class)]
class Variable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_fin_inscription = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_ouver_resa = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date_fin_resa = null;

    #[ORM\Column(nullable: true)]
    private ?int $place_session = null;

    #[ORM\Column(nullable: true)]
    private ?int $place_rdv = null;

    #[ORM\Column(nullable: true)]
    private ?int $place_rdv2 = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateFinInscription(): ?\DateTimeInterface
    {
        return $this->date_fin_inscription;
    }

    public function setDateFinInscription(\DateTimeInterface $date_fin_inscription): static
    {
        $this->date_fin_inscription = $date_fin_inscription;

        return $this;
    }

    public function getDateOuverResa(): ?\DateTimeInterface
    {
        return $this->date_ouver_resa;
    }

    public function setDateOuverResa(?\DateTimeInterface $date_ouver_resa): static
    {
        $this->date_ouver_resa = $date_ouver_resa;

        return $this;
    }

    public function getDateFinResa(): ?\DateTimeInterface
    {
        return $this->date_fin_resa;
    }

    public function setDateFinResa(?\DateTimeInterface $date_fin_resa): static
    {
        $this->date_fin_resa = $date_fin_resa;

        return $this;
    }

    public function getPlaceSession(): ?int
    {
        return $this->place_session;
    }

    public function setPlaceSession(?int $place_session): static
    {
        $this->place_session = $place_session;

        return $this;
    }

    public function getPlaceRdv(): ?int
    {
        return $this->place_rdv;
    }

    public function setPlaceRdv(?int $place_rdv): static
    {
        $this->place_rdv = $place_rdv;

        return $this;
    }

    public function getPlaceRdv2(): ?int
    {
        return $this->place_rdv2;
    }

    public function setPlaceRdv2(?int $place_rdv2): static
    {
        $this->place_rdv2 = $place_rdv2;

        return $this;
    }
}
