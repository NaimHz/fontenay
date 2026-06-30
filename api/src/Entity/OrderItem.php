<?php

namespace App\Entity;

use App\Enum\OrderItemStatus;
use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;

/** Une ligne de commande : un plat, une quantité, un suivi cuisine. */
#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Order $order = null;

    #[ORM\ManyToOne(targetEntity: Dish::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Dish $dish = null;

    #[ORM\Column]
    private int $quantity = 1;

    /** Numéro de couvert (prise de commande par couvert). Null = table entière. */
    #[ORM\Column(nullable: true)]
    private ?int $seatNumber = null;

    #[ORM\Column(enumType: OrderItemStatus::class)]
    private OrderItemStatus $status = OrderItemStatus::PENDING;

    /** Prix unitaire figé au moment de la commande. */
    #[ORM\Column(type: 'decimal', precision: 6, scale: 2)]
    private string $unitPrice = '0.00';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getDish(): ?Dish
    {
        return $this->dish;
    }

    public function setDish(?Dish $dish): self
    {
        $this->dish = $dish;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getSeatNumber(): ?int
    {
        return $this->seatNumber;
    }

    public function setSeatNumber(?int $seatNumber): self
    {
        $this->seatNumber = $seatNumber;

        return $this;
    }

    public function getStatus(): OrderItemStatus
    {
        return $this->status;
    }

    public function setStatus(OrderItemStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getUnitPrice(): string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): self
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }
}
