<?php

namespace App\Entity;

use App\Repository\CategorieClubRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;



/**
 * @ORM\Entity(repositoryClass=CategorieClubRepository::class)
 */
class CategorieClub
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le champ 'Catégorie' ne peut pas etre vide.")

     */
    private $categorieNom;

    /**
     * @ORM\OneToMany (targetEntity=Club::class,mappedBy="clubCategorie",orphanRemoval=true)
     */
    private $clubs;

    public function __construct()
    {
        $this->clubs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategorieNom(): ?string
    {
        return $this->categorieNom;
    }

    public function setCategorieNom(?string $categorieNom): self
    {
        $this->categorieNom = $categorieNom;

        return $this;
    }
    /**
     * @return Collection|Club[]
     */
    public function getClubs(): Collection
    {
        return $this->clubs;
    }

    public function addClub(Club $club): self
    {
        if (!$this->clubs->contains($club)) {
            $this->clubs[] = $club;
            $club->setClubCategorie($this);
        }

        return $this;
    }

    public function removeClub(Club $club): self
    {
        if ($this->clubs->removeElement($club)) {
            // set the owning side to null (unless already changed)
            if ($club->getClubCategorie() === $this) {
                $club->setClubCategorie(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getCategorieNom() ;   }

}
