<?php

namespace App\Entity;

use App\Repository\ClubRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClubRepository::class)
 */
class Club
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $clubNom;



    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $clubDescription;

    /**
     * @ORM\ManyToOne(targetEntity=CategorieClub::class,inversedBy="clubs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $clubCategorie;

    /**
     * @ORM\OneToMany(targetEntity=ClubPub::class, mappedBy="club", orphanRemoval=true)
     */
    private $clubPubs;

    /**
     * @ORM\OneToOne(targetEntity=User::class, cascade={"persist", "remove"})
     */
    private $clubResponsable;

    public function __construct()
    {
        $this->clubPubs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClubNom(): ?string
    {
        return $this->clubNom;
    }

    public function setClubNom(string $clubNom): self
    {
        $this->clubNom = $clubNom;

        return $this;
    }

    public function getClubDescription(): ?string
    {
        return $this->clubDescription;
    }

    public function setClubDescription(?string $clubDescription): self
    {
        $this->clubDescription = $clubDescription;

        return $this;
    }

    public function getClubCategorie(): ?CategorieClub
    {
        return $this->clubCategorie;
    }

    public function setClubCategorie(?CategorieClub $clubCategorie): self
    {
        $this->clubCategorie = $clubCategorie;

        return $this;
    }

    /**
     * @return Collection|ClubPub[]
     */
    public function getClubPubs(): Collection
    {
        return $this->clubPubs;
    }

    public function addClubPub(ClubPub $clubPub): self
    {
        if (!$this->clubPubs->contains($clubPub)) {
            $this->clubPubs[] = $clubPub;
            $clubPub->setClub($this);
        }

        return $this;
    }

    public function removeClubPub(ClubPub $clubPub): self
    {
        if ($this->clubPubs->removeElement($clubPub)) {
            // set the owning side to null (unless already changed)
            if ($clubPub->getClub() === $this) {
                $clubPub->setClub(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getClubNom();
    }

    public function getClubResponsable(): ?User
    {
        return $this->clubResponsable;
    }

    public function setClubResponsable(?User $clubResponsable): self
    {
        $this->clubResponsable = $clubResponsable;

        return $this;
    }
}
