<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CompteRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=CompteRepository::class)
 * @UniqueEntity(
 *      fields={"email"},
 *      message="L'email que vous avez indique est deja utilise !"
 * )
 */
class Compte implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Username;

    /**
     * @ORM\Column(type="string", length=255) 
     * @Assert\Email(
     *     message = "Entrez un email valide."
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length( min = 8 , minMessage="Votre mot de passe est trop court."
     * )
     */
    private $password;
    /** 
     * @Assert\EqualTo(
     *     propertyPath="password", message="Vueillez confirmer avec un mot de passe valide."
     * )
     */
    private $confirm_password;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Type;

    /**
     * @ORM\OneToOne(targetEntity=Client::class, cascade={"persist", "remove"})
     */
    private $idClient;

    /**
     * @ORM\OneToOne(targetEntity=Technicien::class, cascade={"persist", "remove"})
     */
    private $idTech;

    /**
     * @ORM\OneToOne(targetEntity=Admin::class, cascade={"persist", "remove"})
     */
    private $idAdmin;

    public function eraseCredentials(){}

    public function getSalt(){}

    public function getRoles() : array
    {
        return ['ROLE_USER'];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->Username;
    }

    public function setUsername(string $Username): self
    {
        $this->Username = $Username;

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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->Type;
    }

    public function setType(string $Type): self
    {
        $this->Type = $Type;

        return $this;
    }

    public function getIdClient(): ?Client
    {
        return $this->idClient;
    }

    public function setIdClient(?Client $idClient): self
    {
        $this->idClient = $idClient;

        return $this;
    }

    public function getIdTech(): ?technicien
    {
        return $this->idTech;
    }

    public function setIdTech(?technicien $idTech): self
    {
        $this->idTech = $idTech;

        return $this;
    }
    public function getConfirmPassword(): ?string
    {
        return $this->confirm_password;
    }

    public function setConfirmPassword(string $onfirm_password): self
    {
        $this->onfirm_password = $onfirm_password;

        return $this;
    }
    public function __toString()
    {
        $idC = $this->id." ";
        return $idC;
    }

    public function getIdAdmin(): ?Admin
    {
        return $this->idAdmin;
    }

    public function setIdAdmin(?Admin $idAdmin): self
    {
        $this->idAdmin = $idAdmin;

        return $this;
    }
}
