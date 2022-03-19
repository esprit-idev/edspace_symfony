<?php

namespace App\Entity;

use App\Repository\MatiereRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=MatiereRepository::class)
 */
class Matiere
{
    // pattern="/^[A-Za-z0-9_.]*$/",
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le nom de la matière est requis")
     * @Assert\Regex(
     *     pattern="/^\s*[a-zA-Zéèçê0-9]+\s*$/",
     *     message="Veuillez saisir un nom valide (ex:CCCA3)"
     * )
     * @Groups("post:read")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="matiere")
     */
    private $documents;

    /**
     * @ORM\ManyToOne(targetEntity=Niveau::class, inversedBy="matieres")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank(message="Le choix du niveau est requis")
     * @Groups("post:read")
     */
    private $niveau;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setMatiere($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getMatiere() === $this) {
                $document->setMatiere(null);
            }
        }

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

    public function __toString()
    {
        return $this->getId();
    }


}
