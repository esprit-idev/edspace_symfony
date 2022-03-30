<?php

namespace App\Entity;

use App\Repository\CategorieNewsRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
/**
 * @ORM\Entity(repositoryClass=CategorieNewsRepository::class)
 */
class CategorieNews
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("categories")
     */
    private $id;

     /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="ajouter un nom pour la catégorie")
     * @Groups("categories")
     */
    private $categoryName;

    /**
     * @ORM\OneToMany(targetEntity=PublicationNews::class, mappedBy="categoryName")
     * @Groups("pubs")
     */
    private $publicationNews;

    public function __construct()
    {
        $this->publicationNews = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|PublicationNews[]
     */
    public function getPublicationNews(): Collection
    {
        return $this->publicationNews;
    }

    public function addPublicationNews(PublicationNews $publicationNews): self
    {
        if (!$this->publicationNews->contains($publicationNews)) {
            $this->publicationNews[] = $publicationNews;
            $publicationNews->setCategoryName($this);
        }

        return $this;
    }

    public function removePublicationNews(PublicationNews $publicationNews): self
    {
        if ($this->publicationNews->removeElement($publicationNews)) {
            // set the owning side to null (unless already changed)
            if ($publicationNews->getCategoryName() === $this) {
                $publicationNews->setCategoryName(null);
            }
        }

        return $this;
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
    public function __toString() {
        return $this->getCategoryName();
    }
}
