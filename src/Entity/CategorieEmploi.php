<?php

namespace App\Entity;

use App\Repository\CategorieEmploiRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CategorieEmploiRepository::class)
 */
class CategorieEmploi
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("categoriesEmploi")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     * @Assert\NotBlank(message="ajouter le nom de categorie")
     * @Groups("categoriesEmploi")
     */
    private $categoryName;

    /**
     * @ORM\OneToMany(targetEntity=Emploi::class, mappedBy="categoryName")
     * @Groups("offre")
     */
    private $emplois;

    public function __construct()
    {
        $this->emplois = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    public function setCategoryName(?string $categoryName): self
    {
        $this->categoryName = $categoryName;

        return $this;
    }

    /**
     * @return Collection|Emploi[]
     */
    public function getEmplois(): Collection
    {
        return $this->emplois;
    }

    public function addEmploi(Emploi $emploi): self
    {
        if (!$this->emplois->contains($emploi)) {
            $this->emplois[] = $emploi;
            $emploi->setCategoryName($this);
        }

        return $this;
    }

    public function removeEmploi(Emploi $emploi): self
    {
        if ($this->emplois->removeElement($emploi)) {
            // set the owning side to null (unless already changed)
            if ($emploi->getCategoryName() === $this) {
                $emploi->setCategoryName(null);
            }
        }

        return $this;
    }
    public function __toString() {
        return $this->getCategoryName();
    }
}
