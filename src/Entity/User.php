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
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isBanned;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $banDuration;

    /**
     * @ORM\Column(type="boolean")
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity=Classe::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $classe;

    /**
     * @ORM\OneToMany(targetEntity=Thread::class, mappedBy="user", orphanRemoval=true)
     */
    private $threads;

    public function __construct()
    {
        $this->threads = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
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

    public function getRole(): ?bool
    {
        return $this->role;
    }

    public function setRole(bool $role): self
    {
        $this->role = $role;

        return $this;
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
}
