<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, TicketResponse>
     */
    #[ORM\OneToMany(targetEntity: TicketResponse::class, mappedBy: 'ticket')]
    private Collection $ticketResponses;

    public function __construct()
    {
        $this->ticketResponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, TicketResponse>
     */
    public function getTicketResponses(): Collection
    {
        return $this->ticketResponses;
    }

    public function addTicketResponse(TicketResponse $ticketResponse): static
    {
        if (!$this->ticketResponses->contains($ticketResponse)) {
            $this->ticketResponses->add($ticketResponse);
            $ticketResponse->setTicket($this);
        }

        return $this;
    }

    public function removeTicketResponse(TicketResponse $ticketResponse): static
    {
        if ($this->ticketResponses->removeElement($ticketResponse)) {
            // set the owning side to null (unless already changed)
            if ($ticketResponse->getTicket() === $this) {
                $ticketResponse->setTicket(null);
            }
        }

        return $this;
    }
}
