<?php

namespace App\Entity;

use App\Repository\FiltreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FiltreRepository::class)]
class Filtre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'filtre', targetEntity: Site::class)]
    private Collection $Site;

    #[ORM\Column(length: 255)]
    private ?string $nomSortieContient = null;

    public function __construct()
    {
        $this->Site = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Site>
     */
    public function getSite(): Collection
    {
        return $this->Site;
    }

    public function addSite(Site $site): self
    {
        if (!$this->Site->contains($site)) {
            $this->Site->add($site);
            $site->setFiltre($this);
        }

        return $this;
    }

    public function removeSite(Site $site): self
    {
        if ($this->Site->removeElement($site)) {
            // set the owning side to null (unless already changed)
            if ($site->getFiltre() === $this) {
                $site->setFiltre(null);
            }
        }

        return $this;
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
}
