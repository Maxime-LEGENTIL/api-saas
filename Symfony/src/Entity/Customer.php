<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['customers:read', 'customers:post', 'orders:create'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le nom doit être renseigné.")]
    #[Groups(['customers:read', 'customers:post', 'orders:create'])]
    private ?string $firstname = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le prénom doit être renseigné.")]
    #[Groups(['customers:read', 'customers:post', 'orders:create'])]
    private ?string $lastname = null;

    #[ORM\Column(length: 320)]
    #[Assert\Email(message: 'L\'adresse email {{ value }} est invalide.',)]
    #[Groups(['customers:read', 'customers:post', 'orders:create'])]
    private ?string $email = null;

    #[ORM\Column(type: Types::BIGINT)]
    #[Assert\Positive(message: "Le numéro de téléphone doit être un nombre positif.")]
    #[Assert\NotBlank(message: "Le numéro de téléphone doit être renseigné.")]
    #[Groups(['customers:read', 'customers:post', 'orders:create'])]
    private ?string $phonenumber = null;

    #[ORM\Column(length: 320)]
    #[Assert\NotBlank(message: "L'adresse doit être renseignée.")]
    #[Groups(['customers:read', 'customers:post', 'orders:create'])]
    private ?string $address = null;

    #[ORM\Column]
    #[Groups(['customers:read', 'customers:post', 'orders:create'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['customers:read', 'customers:post', 'orders:create'])]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Order>
     */
    #[Groups(['customers:read', 'customers:post', 'orders:create'])]
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'customer')]
    private Collection $orders;

    public function __construct()
    {
        $this->setCreatedAt(new DateTimeImmutable());
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPhonenumber(): ?string
    {
        return $this->phonenumber;
    }

    public function setPhonenumber(string $phonenumber): static
    {
        $this->phonenumber = $phonenumber;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

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
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setCustomer($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getCustomer() === $this) {
                $order->setCustomer(null);
            }
        }

        return $this;
    }
}
