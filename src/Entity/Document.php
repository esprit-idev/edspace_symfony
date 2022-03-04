<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ORM\Entity(repositoryClass=DocumentRepository::class)
 */
class Document
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le nom du dcument est requis")
     * @Groups("post:read")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $date_insert;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $proprietaire;

    /**
     * @ORM\Column(type="blob")
     * @Assert\NotBlank(message="Veuillez attacher un fichier")
     */
    private $fichier;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("post:read")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity=Matiere::class, inversedBy="documents")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(message="Le choix d'une matière est requis")
     * @Groups("post:read")
     */
    private $matiere;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class, inversedBy="documents")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(message="Le choix d'un niveau est requis")
     * @Groups("post:read")
     */
    private $niveau;

    /**
     * @ORM\Column(type="integer")
     * @Groups("post:read")
     */
    private $signalements;

    /**
     * @ORM\OneToMany(targetEntity=DocumentFavoris::class, mappedBy="document", orphanRemoval=true)
     */
    private $documentFavoris;


    public function __construct()
    {
        $this->matieres=new ArrayCollection();
        $this->documentFavoris = new ArrayCollection();
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

    public function setNom(?string $nom): self
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

    public function getSignalements(): ?int
    {
        return $this->signalements;
    }

    public function setSignalements(int $signalements): self
    {
        $this->signalements = 0;
        return $this;
    }

    /**
     * @return Collection<int, DocumentFavoris>
     */
    public function getDocumentFavoris(): Collection
    {
        return $this->documentFavoris;
    }

    public function addDocumentFavori(DocumentFavoris $documentFavori): self
    {
        if (!$this->documentFavoris->contains($documentFavori)) {
            $this->documentFavoris[] = $documentFavori;
            $documentFavori->setDocument($this);
        }

        return $this;
    }

    public function removeDocumentFavori(DocumentFavoris $documentFavori): self
    {
        if ($this->documentFavoris->removeElement($documentFavori)) {
            // set the owning side to null (unless already changed)
            if ($documentFavori->getDocument() === $this) {
                $documentFavori->setDocument(null);
            }
        }

        return $this;
    }
}
