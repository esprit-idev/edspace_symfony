<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 */
class Document
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le nom du dcument est requis")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $date_insert;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $proprietaire;

    /**
     * @ORM\Column(type="blob")
     * @Assert\NotBlank(message="Veuillez attacher un fichier")
     */
    private $fichier;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Matiere::class, inversedBy="documents")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(message="Le choix d'une matière est requis")
     */
    private $matiere;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class, inversedBy="documents")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(message="Le choix d'un niveau est requis")
     */
    private $niveau;


    public function __construct()
    {
        $this->matieres=new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFichier()
    {
        return $this->fichier;
    }

    public function setFichier($fichier)
    {
        $this->fichier = $fichier;

        return $this;
    }

    public function getDateInsert(): ?string
    {
        return $this->date_insert;
    }

    public function setDateInsert(string $date_insert): self
    {
        $this->date_insert = $date_insert;

        return $this;
    }

    public function getProprietaire(): ?string
    {
        return $this->proprietaire;
    }

    public function setProprietaire(string $proprietaire): self
    {
        $this->proprietaire = $proprietaire;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getMatiere(): ?Matiere
    {
        return $this->matiere;
    }

    public function setMatiere(?Matiere $matiere): self
    {
        $this->matiere = $matiere;

        return $this;
    }


    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(?Niveau $niveau): self
    {
        $this->niveau = $niveau;

        return $this;
    }
}
