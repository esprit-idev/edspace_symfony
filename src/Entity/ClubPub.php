<?php

namespace App\Entity;

use App\Repository\ClubPubRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Serializer\Annotation\Groups;

use function PHPUnit\Framework\isEmpty;



/**
 * @ORM\Entity(repositoryClass=ClubPubRepository::class)
 */
class ClubPub
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("post:read")

     */
    private $id;

    /**
     * @ORM\Column(type="datetime",nullable=true)
     * @Groups("post:read")

     */
    private $pubDate;

    /**
     * @ORM\Column(type="string", length=1000, nullable=false)
     * @Assert\NotBlank(message="Le champ 'Description' ne peut pas etre vide.")
     * @Groups("post:read")

     */
    private $pubDescription;

    /**
     * @ORM\Column(type="blob",nullable=true)
     */
    private $pubFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("post:read")
	 */
    private $pubFileName;
    /**
     * @ORM\Column(type="string", length=255,nullable=true)
     */
    private $typeFichier;


    /**
     * @ORM\ManyToOne(targetEntity=Club::class, inversedBy="clubPubs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("post:read")

     */
    private $club;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("post:read")
     */
    private $ClubImg;

    /**
     * @ORM\Column(type="integer")
     * @Groups("post:read")

     */
    private $isPosted;




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPubDate(): ?\DateTimeInterface
    {
        return $this->pubDate;
    }

    public function setPubDate(\DateTimeInterface $pubDate): self
    {
        $this->pubDate = $pubDate;

        return $this;
    }

    public function getPubDescription(): ?string
    {
        return $this->pubDescription;
    }

    public function setPubDescription(?string $pubDescription): self
    {
        $this->pubDescription = $pubDescription;

        return $this;
    }

    public function getPubFile()
    {
        return $this->pubFile;
    }

    public function setPubFile($pubFile): self
    {
        $this->pubFile = $pubFile;

        return $this;
    }

    public function getClub(): ?Club
    {
        return $this->club;
    }

    public function setClub(?Club $club): self
    {
        $this->club = $club;

        return $this;
    }



    public function getTypeFichier(): ?string
    {
        return $this->typeFichier;
    }

    public function setTypeFichier(string $typeFichier): self
    {
        $this->typeFichier = $typeFichier;
        return $this;
    }


    public function getClubImg()
    {
        return $this->ClubImg;
    }

    public function setClubImg($ClubImg)
    {
        $this->ClubImg = $ClubImg;

        return $this;
    }

    public function getPubFileName(): ?string
    {
        return $this->pubFileName;
    }

    public function setPubFileName(?string $pubFileName): self
    {
        $this->pubFileName = $pubFileName;

        return $this;
    }

    public function getIsPosted(): ?int
    {
        return $this->isPosted;
    }

    public function setIsPosted(int $isPosted): self
    {
        $this->isPosted = $isPosted;

        return $this;
    }

}
