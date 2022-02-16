<?php

namespace App\Entity;

use App\Repository\ClubPubRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClubPubRepository::class)
 */
class ClubPub
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $pubDate;

    /**
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    private $pubDescription;

    /**
     * @ORM\Column(type="blob", nullable=true)
     */
    private $pubFile;

    /**
     * @ORM\ManyToOne(targetEntity=Club::class, inversedBy="clubPubs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $club;

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
}
