<?php

namespace App\Entity;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\PublicationNewsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PublicationNewsRepository::class)
 */
class PublicationNews
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("publications")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     * @Groups("publications")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=55)
     * @Assert\NotBlank(message="le titre est requi")
     * @Groups("publications")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=55, nullable=true)
     * @Assert\NotBlank(message="le titre est requi")
     * @Groups("publications")
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity=CategorieNews::class, inversedBy="publicationNews")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Assert\NotBlank(message="le titre est requi")
     * @Groups("publications")
     */
    private $categoryName;

    /**
     * @ORM\OneToOne(targetEntity=Image::class, cascade={"persist", "remove"})
     * @Assert\NotBlank()
     * @Groups("pubimage")
     */
    private $image;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default" : 0})
     * @Groups("publications")
     */
    private $likes;

    /**
     * @ORM\Column(type="string", length=6500, nullable=true)
     * @Groups("publications")
     */
    private $comments;

    /**
     * @ORM\Column(type="integer", nullable=true, options={"default" : 0})
     * @Groups("publications")
     */
    private $vues;

    /**
     * @ORM\Column(type="string", length=6500, nullable=true)
     * @Groups("publications")
     */
    private $content;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCategoryName(): ?CategorieNews
    {
        return $this->categoryName;
    }

    public function setCategoryName(?CategorieNews $categoryName): self
    {
        $this->categoryName = $categoryName;

        return $this;
    }
    
    public function __toString()
    {
        return (string) $this->getCategoryName();
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes($likes): self
    {
        if (!$this->likes->contains($likes)) {
            $this->likes[] = $likes;
        }

        return $this;
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments): self
    {
        $this->comments = $comments;

        return $this;
    }

    public function getVues(): ?int
    {
        return $this->vues;
    }

    public function setVues($vues): self
    {
        $this->vues = $vues;

        return $this;
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

    public function increment(){
       return ++ $this->vues;
    }
    public function incrementLikes(){
        return ++ $this->likes;
     }
}
