<?php

namespace App\Entity;

use App\Repository\CategorieNewsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategorieNewsRepository::class)
 */
class CategorieNews
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
    private $catName;

    /**
     * @ORM\OneToMany(targetEntity=PublicationNews::class, mappedBy="categorieNews")
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

    public function getCatName(): ?string
    {
        return $this->catName;
    }

    public function setCatName(string $catName): self
    {
        $this->catName = $catName;

        return $this;
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
            $publicationNews->setCategorieNews($this);
        }

        return $this;
    }

    public function removePublicationNews(PublicationNews $publicationNews): self
    {
        if ($this->publicationNews->removeElement($publicationNews)) {
            // set the owning side to null (unless already changed)
            if ($publicationNews->getCategorieNews() === $this) {
                $publicationNews->setCategorieNews(null);
            }
        }

        return $this;
    }
}
