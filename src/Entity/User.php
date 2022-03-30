<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(
 *     fields={"email"},
 *     message="l'email que vous avez indiqué est deja utulisé"
 * )
*/
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("students")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="merci de saisir le nom")
     * @Groups("students")
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="merci de saisir le prenom ")
     * @Groups("students")
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)

     * @Groups("students")
     */
    private $email;

    /**
     * @ORM\OneToOne(targetEntity="Club")
     * @ORM\JoinColumn(nullable=true)
     * @Groups("students")
     */
    protected $club;
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="merci de saisir le mot de passe ")
     * @Assert\Length(min="8",minMessage="votre mot de passe doit faire minimum 8 caractéres")
     * @Groups("students")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups("students")
     *
     */
    private $isBanned;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("students")
     */
    private $banDuration;

    /**
     * @ORM\Column(type="json",nullable=true)

     * @Groups("students")
     */
    private $roles =[];

    /**
     * @ORM\ManyToOne(targetEntity=Classe::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups("students")
     */
    private $classe;

    /**
     * @ORM\OneToMany(targetEntity=Thread::class, mappedBy="user", orphanRemoval=true)
     * @Groups("students")
     */
    private $threads;

    /**
     * @ORM\OneToMany(targetEntity=DocumentFavoris::class, mappedBy="user", orphanRemoval=true)
     * @Groups("students")
     */
    private $documentsFavoris;
    /**
     * @ORM\OneToMany(targetEntity=Reponse::class, mappedBy="user", orphanRemoval=true)
     * @Groups("students")
     */
    private $reponses;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="user")
     * @Groups("students")
     */
    private $message;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("students")
     */
    private $reset_token;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\File(mimeTypes={"image/png", "image/jpeg"})
     * @Groups("students")
     */
    private $image;

    public function __construct()
    {
        $this->threads = new ArrayCollection();
        $this->documentsFavoris = new ArrayCollection();
        $this->reponses = new ArrayCollection();
        $this->message = new ArrayCollection();
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
        }
        return $this;

    }
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
        }

        return $this;
    }

    public function removeDocumentsFavori(DocumentFavoris $documentsFavori): self
    {
        if ($this->documentsFavoris->removeElement($documentsFavori)) {
            // set the owning side to null (unless already changed)
            if ($documentsFavori->getUser() === $this) {
                $documentsFavori->setUser(null);
            }
        }
        return $this;
    }
    public function removeReponse(Reponse $reponse): self
    {
        if ($this->reponses->removeElement($reponse)) {
            // set the owning side to null (unless already changed)
            if ($reponse->getUser() === $this) {
                $reponse->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getMessage(): Collection
    {
        return $this->message;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->message->contains($message)) {
            $this->message[] = $message;
            $message->setUser($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->message->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getUser() === $this) {
                $message->setUser(null);
            }
        }

        return $this;
    }
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $reset_token): self
    {
        $this->reset_token = $reset_token;

        return $this;
    }

    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set thumbnail.
     *
     * @param string $image
     *
     * @return Post
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }
    
}
