<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\PublicationNewsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PublicationNewsRepository::class)
 */
class PublicationNews
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $content;


    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=CategorieNews::class, inversedBy="publicationNews")
     * @ORM\JoinColumn(nullable=true)
     */
    private $categorieNews;

    /**
     * @ORM\Column(type="string", length=55)
     * @Assert\NotBlank(message="le titre est requi")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     * @Assert\NotBlank(message="le titre est requi")
     */
    private $owner;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
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

    public function getCategorieNews(): ?CategorieNews
    {
        return $this->categorieNews;
    }

    public function setCategorieNews(?CategorieNews $categorieNews): self
    {
        $this->categorieNews = $categorieNews;

        return $this;
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

    public function getOwner(): ?string
    {
        return $this->owner;
    }

    public function setOwner(?string $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
    public function __toString()
    {
        return (string) $this->getCategorieNews();
    }
}
