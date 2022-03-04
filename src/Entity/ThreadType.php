<?php

namespace App\Entity;

use App\Repository\ThreadTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ThreadTypeRepository::class)
 */
class ThreadType
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $content;

    /**
     * @ORM\OneToMany(targetEntity=Thread::class, mappedBy="threadType", orphanRemoval=true)
     */
    private $thread;

    /**
     * @ORM\Column(type="boolean")
     */
    private $display;

    public function __construct()
    {
        $this->thread = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return Collection|Thread[]
     */
    public function getThread(): Collection
    {
        return $this->thread;
    }

    public function addThread(Thread $thread): self
    {
        if (!$this->thread->contains($thread)) {
            $this->thread[] = $thread;
            $thread->setThreadType($this);
        }

        return $this;
    }

    public function removeThread(Thread $thread): self
    {
        if ($this->thread->removeElement($thread)) {
            // set the owning side to null (unless already changed)
            if ($thread->getThreadType() === $this) {
                $thread->setThreadType(null);
            }
        }

        return $this;
    }

    public function __toString(){
        return $this->content;
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
}
