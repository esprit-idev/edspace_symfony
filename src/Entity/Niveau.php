<?php

namespace App\Entity;

use App\Repository\NiveauRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=NiveauRepository::class)
 */
class Niveau
{
    /*
    * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="[0-9]+/[a-zA-Z]/[0-9]+",
     *     match=false,
     *     message="Your property should match ..."
     * ) */
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le niveau est requis")
     * @Assert\Regex(
     *     pattern="/^[0-9a-zA-Z]/",
     *     message="Veuillez saisir un niveau d'étude valide (ex:4SIM)"
     * )
     * @Groups("post:read")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity=Document::class, mappedBy="niveau")
     */
    private $documents;

    /**
     * @ORM\OneToMany(targetEntity=Matiere::class, mappedBy="niveau")
     */
    private $matieres;

    /**
     * @ORM\OneToMany(targetEntity=Classe::class, mappedBy="niveau", orphanRemoval=true)
     */
    private $classes;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
        $this->matieres = new ArrayCollection();
        $this->classes = new ArrayCollection();
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
            $document->setNiveau($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            // set the owning side to null (unless already changed)
            if ($document->getNiveau() === $this) {
                $document->setNiveau(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Matiere[]
     */
    public function getMatieres(): Collection
    {
        return $this->matieres;
    }

    public function addMatiere(Matiere $matiere): self
    {
        if (!$this->matieres->contains($matiere)) {
            $this->matieres[] = $matiere;
            $matiere->setNiveau($this);
        }

        return $this;
    }

    public function removeMatiere(Matiere $matiere): self
    {
        if ($this->matieres->removeElement($matiere)) {
            // set the owning side to null (unless already changed)
            if ($matiere->getNiveau() === $this) {
                $matiere->setNiveau(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Classe[]
     */
    public function getClasses(): Collection
    {
        return $this->classes;
    }

    public function addClass(Classe $class): self
    {
        if (!$this->classes->contains($class)) {
            $this->classes[] = $class;
            $class->setNiveau($this);
        }

        return $this;
    }

    public function removeClass(Classe $class): self
    {
        if ($this->classes->removeElement($class)) {
            // set the owning side to null (unless already changed)
            if ($class->getNiveau() === $this) {
                $class->setNiveau(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getId();
    }


}
