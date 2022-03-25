<?php

namespace App\Entity;

use App\Repository\EmploiRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=EmploiRepository::class)
 */
class Emploi
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("emplois")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="le titre est requi")
     * @Groups("emplois")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="ajouter une discription")
     * @Groups("emplois")
     */
    private $content;

    /**
     * @ORM\Column(type="date")
     * @Groups("emplois")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=CategorieEmploi::class, inversedBy="emplois")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Assert\NotBlank(message="add category !")
     * @Groups("emplois")
     */
    private $categoryName;

     /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("emploiimg")
     */
    private $image;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getCategoryName(): ?CategorieEmploi
    {
        return $this->categoryName;
    }

    public function setCategoryName(?CategorieEmploi $categoryName): self
    {
        $this->categoryName = $categoryName;

        return $this;
    }
    public function __toString()
    {
        return (string) $this->getCategoryName();
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }
}
