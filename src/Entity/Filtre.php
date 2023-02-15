<?php

namespace App\Entity;

use App\Repository\FiltreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FiltreRepository::class)]
class Filtre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomSortieContient = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $DateFiltreDebut = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateFiltreFin = null;

    #[ORM\Column(nullable: true)]
    private ?bool $organisateur = null;

    #[ORM\Column(nullable: true)]
    private ?bool $inscrit = null;

    #[ORM\Column(nullable: true)]
    private ?bool $pasInscrit = null;

    #[ORM\Column(nullable: true)]
    private ?bool $sortiesPassees = null;

    public function __construct()
    {
        $this->site = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getNomSortieContient(): ?string
    {
        return $this->nomSortieContient;
    }

    public function setNomSortieContient(string $nomSortieContient): self
    {
        $this->nomSortieContient = $nomSortieContient;

        return $this;
    }

    public function getDateFiltreDebut(): ?\DateTimeInterface
    {
        return $this->DateFiltreDebut;
    }

    public function setDateFiltreDebut(?\DateTimeInterface $DateFiltreDebut): self
    {
        $this->DateFiltreDebut = $DateFiltreDebut;

        return $this;
    }

    public function getDateFiltreFin(): ?\DateTimeInterface
    {
        return $this->dateFiltreFin;
    }

    public function setDateFiltreFin(?\DateTimeInterface $dateFiltreFin): self
    {
        $this->dateFiltreFin = $dateFiltreFin;

        return $this;
    }

    public function isOrganisateur(): ?bool
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?bool $organisateur): self
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    public function isInscrit(): ?bool
    {
        return $this->inscrit;
    }

    public function setInscrit(?bool $inscrit): self
    {
        $this->inscrit = $inscrit;

        return $this;
    }

    public function isPasInscrit(): ?bool
    {
        return $this->pasInscrit;
    }

    public function setPasInscrit(bool $pasInscrit): self
    {
        $this->pasInscrit = $pasInscrit;

        return $this;
    }

    public function isSortiesPassees(): ?bool
    {
        return $this->sortiesPassees;
    }

    public function SetSortiesPassees(?bool $sortiesPassees): self
    {
        $this->sortiesPassees = $sortiesPassees;

        return $this;
    }
}
