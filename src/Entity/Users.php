<?php

namespace App\Entity;

use App\Repository\UsersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=UsersRepository::class)
 * @UniqueEntity(
 *     fields={"email"},
 *     message="Un compte est déjà existant sur cette adresse mail"
 * )
 * @method string getUserIdentifier()
 */
class Users implements UserInterface,PasswordAuthenticatedUserInterface
{
    // Pour gérer l'authentification, symfony a besoin que l'entité User implémente la UserInterface contenant les méthodes obligatoires suivante:
    //getRoles()
    //getUsername()
    //getPassword()
    //getUserIdentifier()
    //eraseCredentials() qui permet de supprimer en BDD tout les inserts dont les mots de passes ne sont pas cryptés
    //getSalt() qui permet de retourner en Texte brut les mots de passes hashés en BDD

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     */
    private $id;



    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veillez renseigner ce champs")
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veillez renseigner ce champs")
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veillez renseigner ce champs")
     * @Assert\Email(message="Email invalide")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veillez renseigner ce champs")
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Veillez renseigner ce champs")
     * @Assert\EqualTo(propertyPath="confirmPassword", message="Les mots de passes ne correspondent pas")
     */
    private $password;

    public $confirmPassword;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = ["ROLE_USER"];

    /**
     * @ORM\OneToMany(targetEntity=Movies::class, mappedBy="CreatedBy")
     */
    private $createdMovies;

    /**
     * @ORM\OneToMany(targetEntity=Reviews::class, mappedBy="createdBy")
     */
    private $reviews;

    public function __construct()
    {
        $this->createdMovies = new ArrayCollection();
        $this->reviews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function __call($name, $arguments)
    {
        // TODO: Implement @method string getUserIdentifier()
    }

    /**
     * @return Collection|Movies[]
     */
    public function getCreatedMovies(): Collection
    {
        return $this->createdMovies;
    }

    public function addCreatedMovie(Movies $createdMovie): self
    {
        if (!$this->createdMovies->contains($createdMovie)) {
            $this->createdMovies[] = $createdMovie;
            $createdMovie->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCreatedMovie(Movies $createdMovie): self
    {
        if ($this->createdMovies->removeElement($createdMovie)) {
            // set the owning side to null (unless already changed)
            if ($createdMovie->getCreatedBy() === $this) {
                $createdMovie->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Reviews[]
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Reviews $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setCreatedBy($this);
        }

        return $this;
    }

    public function removeReview(Reviews $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getCreatedBy() === $this) {
                $review->setCreatedBy(null);
            }
        }

        return $this;
    }
}
