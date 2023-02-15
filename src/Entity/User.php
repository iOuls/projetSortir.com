<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Il y a déjà un compte créé avec cette adresse mail.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Email(
        message: 'L\'email saisi n\'est pas valide.',
    )]
    #[Assert\NotBlank]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[Assert\NotBlank]
    #[ORM\Column]
    private ?string $password = null;

    #[Assert\Length(max: 255, maxMessage: 'Le champ nom n\'accepte que 255 caractères maximum.')]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Assert\Length(max: 255, maxMessage: 'Le champ prénom n\'accepte que 255 caractères maximum.')]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[Assert\Range(min: 100000000, max: 900000000, notInRangeMessage: 'Le numéro de téléphone est incorrect.')]
    #[Assert\Type('integer')]
    #[Assert\NotBlank]
    #[ORM\Column(nullable: true)]
    private ?int $telephone = null;

    #[Assert\Type('boolean')]
    #[Assert\NotBlank]
    #[ORM\Column]
    private ?bool $administrateur = null;

    #[Assert\Type('boolean')]
    #[Assert\NotBlank]
    #[ORM\Column]
    private ?bool $actif = null;

    #[ORM\OneToMany(mappedBy: 'organisateur', targetEntity: Sortie::class)]
    private Collection $sortiesO;

    #[ORM\ManyToMany(mappedBy: 'participant', targetEntity: Sortie::class)]
    private Collection $sortiesP;

    #[ORM\Column(type: Types::BLOB, nullable: true)]
    private $photo = null;

    #[Assert\Length(max: 255, maxMessage: 'Le champ pseudo n\'accepte que 255 caractères maximum.')]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[ORM\Column(length: 255)]
    private ?string $Pseudo = null;

    #[ORM\ManyToOne(inversedBy: 'user')]
    private ?Site $site = null;

    public function __construct()
    {
        $this->sorties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?int
    {
        return $this->telephone;
    }

    public function setTelephone(?int $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function isAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): self
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function isActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesO(): Collection
    {
        return $this->sortiesO;
    }

    public function addSortieO(Sortie $sortie): self
    {
        if (!$this->sortiesO->contains($sortie)) {
            $this->sortiesO->add($sortie);
            $sortie->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSortieO(Sortie $sortie): self
    {
        if ($this->sortiesO->removeElement($sortie)) {
            // set the owning side to null (unless already changed)
            if ($sortie->getOrganisateur() === $this) {
                $sortie->setOrganisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sortie>
     */
    public function getSortiesP(): Collection
    {
        return $this->sortiesP;
    }

    public function addSortieP(Sortie $sortie): self
    {
        if (!$this->sortiesP->contains($sortie)) {
            $this->sortiesP->add($sortie);
            $sortie->setOrganisateur($this);
        }

        return $this;
    }

    public function removeSortieP(Sortie $sortie): self
    {
        if ($this->sortiesP->removeElement($sortie)) {
            // set the owning side to null (unless already changed)
            if ($sortie->getOrganisateur() === $this) {
                $sortie->setOrganisateur(null);
            }
        }

        return $this;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo): self
    {
        $this->photo = $photo;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->Pseudo;
    }

    public function setPseudo(string $Pseudo): self
    {
        $this->Pseudo = $Pseudo;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }
}
