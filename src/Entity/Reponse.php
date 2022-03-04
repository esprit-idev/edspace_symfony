<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ReponseRepository::class)
 */
class Reponse
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
    private $reply;

    /**
     * @ORM\Column(type="datetime")
     */
    private $replyDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $display;

    /**
     * @ORM\ManyToOne(targetEntity=Thread::class, inversedBy="reponses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $thread;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReply(): ?string
    {
        return $this->reply;
    }

    public function setReply(string $reply): self
    {
        $this->reply = $reply;

        return $this;
    }

    public function getReplyDate(): ?\DateTimeInterface
    {
        return $this->replyDate;
    }

    public function setReplyDate(\DateTimeInterface $replyDate): self
    {
        $this->replyDate = $replyDate;

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

    public function getThread(): ?Thread
    {
        return $this->thread;
    }

    public function setThread(?Thread $thread): self
    {
        $this->thread = $thread;

        return $this;
    }
}
