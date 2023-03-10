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
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[Vich\Uploadable]
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
    #[ORM\Column]
    private ?bool $administrateur = false;

    #[Assert\Type('boolean')]
    #[ORM\Column]
    private ?bool $actif = false;

    #[ORM\OneToMany(mappedBy: 'organisateur', targetEntity: Sortie::class)]
    private Collection $sortiesO;

    #[ORM\ManyToMany(mappedBy: 'participant', targetEntity: Sortie::class)]
    private Collection $sortiesP;


    #[Assert\Length(max: 255, maxMessage: 'Le champ pseudo n\'accepte que 255 caractères maximum.')]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[ORM\Column(length: 255)]
    private ?string $Pseudo = null;

    #[ORM\ManyToOne(inversedBy: 'user')]
    private ?Site $site = null;

    public function __construct()
    {
        $this->sortiesO = new ArrayCollection();
        $this->sortiesP = new ArrayCollection();
        $this->groupes = new ArrayCollection();
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


    public function getPseudo(): ?string
    {
        return $this->Pseudo;
    }

    public function setPseudo(string $Pseudo): self
    {
        $this->Pseudo = $Pseudo;

        return $this;
    }


    #[ORM\Column(nullable: true)]
    private $image;


    #[Vich\UploadableField(mapping: "image", fileNameProperty: "image")]
    private $imageFile;


    #[ORM\Column(nullable: true)] private ?\DateTime $updatedAt;


    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($image) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'imageFile' => base64_encode($this->imageFile),
            'password' => $this->password,
        ];
    }

    public function __unserialize(array $serialized)
    {
        $this->imageFile = base64_decode($serialized['imageFile']);
        $this->email = $serialized['email'];
        $this->id = $serialized['id'];
        $this->password = $serialized['password'];
        return $this;
    }

    // Reset password
    #[ORM\Column(nullable: true, type: 'string', length: 100)]
    private $resetToken = null;

    #[ORM\ManyToMany(targetEntity: Groupe::class, mappedBy: 'participants')]
    private Collection $groupes;

// ...

    public function getResetToken(): ?string
    {
        return $this->resetToken;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->resetToken = $resetToken;

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

    /**
     * @return Collection<int, Groupe>
     */
    public function getGroupes(): Collection
    {
        return $this->groupes;
    }

    public function addGroupe(Groupe $groupe): self
    {
        if (!$this->groupes->contains($groupe)) {
            $this->groupes->add($groupe);
            $groupe->addParticipant($this);
        }

        return $this;
    }

    public function removeGroupe(Groupe $groupe): self
    {
        if ($this->groupes->removeElement($groupe)) {
            $groupe->removeParticipant($this);
        }

        return $this;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('Pseudo', new Assert\Regex([
            'pattern' => '/\d/',
            'match' => false,
            'message' => 'Your name cannot contain a number',
        ]));

    }


}
