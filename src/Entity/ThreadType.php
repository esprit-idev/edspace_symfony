<?php

namespace App\Entity;

use App\Repository\ThreadTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
     */
    private $content;

    /**
     * @ORM\OneToMany(targetEntity=Thread::class, mappedBy="threadType", orphanRemoval=true)
     */
    private $thread;

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
}
