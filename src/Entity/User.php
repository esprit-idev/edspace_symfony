<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
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
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\OneToOne(targetEntity="Club")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $club;
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     *
     */
    private $isBanned;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $banDuration;

    /**
     * @ORM\Column(type="json",nullable=true)
     */
    private $roles =[];

    /**
     * @ORM\ManyToOne(targetEntity=Classe::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $classe;

    /**
     * @ORM\OneToMany(targetEntity=Thread::class, mappedBy="user", orphanRemoval=true)
     */
    private $threads;

    /**
<<<<<<< HEAD
     * @ORM\OneToMany(targetEntity=DocumentFavoris::class, mappedBy="user", orphanRemoval=true)
     */
    private $documentsFavoris;
=======
     * @ORM\OneToMany(targetEntity=Reponse::class, mappedBy="user", orphanRemoval=true)
     */
    private $reponses;
>>>>>>> 6dfa360597e8c07db1995234c0c76df4b4e453cc

    public function __construct()
    {
        $this->threads = new ArrayCollection();
<<<<<<< HEAD
        $this->documentsFavoris = new ArrayCollection();
=======
        $this->reponses = new ArrayCollection();
>>>>>>> 6dfa360597e8c07db1995234c0c76df4b4e453cc
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClub()
    {
        return $this->club;
    }

    /**
     * @param mixed $club
     */
    public function setClub($club): void
    {
        $this->club = $club;
    }

    public function getIsBanned(): ?bool
    {
        return $this->isBanned;
    }

    public function setIsBanned(?bool $isBanned): self
    {
        $this->isBanned = $isBanned;

        return $this;
    }

    public function getBanDuration(): ?int
    {
        return $this->banDuration;
    }

    public function setBanDuration(?int $banDuration): self
    {
        $this->banDuration = $banDuration;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }



    public function getClasse(): ?Classe
    {
        return $this->classe;
    }

    public function setClasse(?Classe $classe): self
    {
        $this->classe = $classe;

        return $this;
    }

    /**
     * @return Collection|Thread[]
     */
    public function getThreads(): Collection
    {
        return $this->threads;
    }

    public function addThread(Thread $thread): self
    {
        if (!$this->threads->contains($thread)) {
            $this->threads[] = $thread;
            $thread->setUser($this);
        }

        return $this;
    }

    public function removeThread(Thread $thread): self
    {
        if ($this->threads->removeElement($thread)) {
            // set the owning side to null (unless already changed)
            if ($thread->getUser() === $this) {
                $thread->setUser(null);
            }
        }

        return $this;
    }
<<<<<<< HEAD

    /**
     * @return Collection<int, DocumentFavoris>
     */
    public function getDocumentsFavoris(): Collection
    {
        return $this->documentsFavoris;
    }

    public function addDocumentsFavori(DocumentFavoris $documentsFavori): self
    {
        if (!$this->documentsFavoris->contains($documentsFavori)) {
            $this->documentsFavoris[] = $documentsFavori;
            $documentsFavori->setUser($this);
=======
    public function __toString(){
        return $this->getEmail();
    }

    /**
     * @return Collection|Reponse[]
     */
    public function getReponses(): Collection
    {
        return $this->reponses;
    }

    public function addReponse(Reponse $reponse): self
    {
        if (!$this->reponses->contains($reponse)) {
            $this->reponses[] = $reponse;
            $reponse->setUser($this);
>>>>>>> 6dfa360597e8c07db1995234c0c76df4b4e453cc
        }

        return $this;
    }

<<<<<<< HEAD
    public function removeDocumentsFavori(DocumentFavoris $documentsFavori): self
    {
        if ($this->documentsFavoris->removeElement($documentsFavori)) {
            // set the owning side to null (unless already changed)
            if ($documentsFavori->getUser() === $this) {
                $documentsFavori->setUser(null);
=======
    public function removeReponse(Reponse $reponse): self
    {
        if ($this->reponses->removeElement($reponse)) {
            // set the owning side to null (unless already changed)
            if ($reponse->getUser() === $this) {
                $reponse->setUser(null);
>>>>>>> 6dfa360597e8c07db1995234c0c76df4b4e453cc
            }
        }

        return $this;
    }
}
