<?php

namespace App\Entity;

use App\Repository\ThreadRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass=ThreadRepository::class)
 */
class Thread
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
     * @Assert\NotBlank
     * @Groups("post:read")
     */
    private $question;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nb_likes;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("post:read")
     */
    private $postDate;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("post:read")
     */
    private $display;

    /**
     * @ORM\OneToMany(targetEntity=Reponse::class, mappedBy="thread", orphanRemoval=true)
     * @Groups("post:read")
     */
    private $reponses;

    /**
     * @ORM\ManyToOne(targetEntity=ThreadType::class, inversedBy="Thread")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("post:read")
     */
    private $threadType;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="threads")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("post:read")
     */
    private $user;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("post:read")
     */
    private $Verified;

    

    public function __construct()
    {
        $this->reponses = new ArrayCollection();
        $this->ThreadReact = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(string $question): self
    {
        $this->question = $question;

        return $this;
    }

    public function getNbLikes(): ?int
    {
        return $this->nb_likes;
    }

    public function setNbLikes(?int $nb_likes): self
    {
        $this->nb_likes = $nb_likes;

        return $this;
    }

    public function getPostDate(): ?\DateTimeInterface
    {
        return $this->postDate;
    }

    public function setPostDate(\DateTimeInterface $postDate): self
    {
        $this->postDate = $postDate;

        return $this;
    }

    public function getDisplay(): ?bool
    {
        return $this->display;
    }

    public function setDisplay(bool $display): self
    {
        $this->display = $display;

        return $this;
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
            $reponse->setThread($this);
        }

        return $this;
    }

    public function removeReponse(Reponse $reponse): self
    {
        if ($this->reponses->removeElement($reponse)) {
            // set the owning side to null (unless already changed)
            if ($reponse->getThread() === $this) {
                $reponse->setThread(null);
            }
        }

        return $this;
    }

    public function getThreadType(): ?ThreadType
    {
        return $this->threadType;
    }

    public function setThreadType(?ThreadType $threadType): self
    {
        $this->threadType = $threadType;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
    public function __toString()
    {
        return $this->getQuestion();
    }

    public function getVerified(): ?bool
    {
        return $this->Verified;
    }

    public function setVerified(bool $Verified): self
    {
        $this->Verified = $Verified;

        return $this;
    }

   
}
