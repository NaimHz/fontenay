<?php

namespace App\Entity;

use App\Enum\ReservationStatus;
use App\Enum\ServiceType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Réservation client. Les allergies et le régime sont saisis dès la
 * réservation pour remonter l'information jusqu'en cuisine.
 */
#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Establishment::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Establishment $establishment = null;

    #[ORM\Column(length: 120)]
    private string $customerName;

    #[ORM\Column(length: 180)]
    private string $customerEmail;

    #[ORM\Column(length: 30)]
    private string $customerPhone;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $date;

    #[ORM\Column(enumType: ServiceType::class)]
    private ServiceType $service;

    #[ORM\Column]
    private int $partySize;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $allergies = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $specialRequest = null;

    #[ORM\Column(enumType: ReservationStatus::class)]
    private ReservationStatus $status = ReservationStatus::PENDING;

    /** Table attribuée à l'arrivée du client (null tant que non installé). */
    #[ORM\ManyToOne(targetEntity: DiningTable::class)]
    private ?DiningTable $diningTable = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEstablishment(): ?Establishment
    {
        return $this->establishment;
    }

    public function setEstablishment(?Establishment $establishment): self
    {
        $this->establishment = $establishment;

        return $this;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): self
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function setCustomerEmail(string $customerEmail): self
    {
        $this->customerEmail = $customerEmail;

        return $this;
    }

    public function getCustomerPhone(): string
    {
        return $this->customerPhone;
    }

    public function setCustomerPhone(string $customerPhone): self
    {
        $this->customerPhone = $customerPhone;

        return $this;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getService(): ServiceType
    {
        return $this->service;
    }

    public function setService(ServiceType $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getPartySize(): int
    {
        return $this->partySize;
    }

    public function setPartySize(int $partySize): self
    {
        $this->partySize = $partySize;

        return $this;
    }

    public function getAllergies(): ?string
    {
        return $this->allergies;
    }

    public function setAllergies(?string $allergies): self
    {
        $this->allergies = $allergies;

        return $this;
    }

    public function getSpecialRequest(): ?string
    {
        return $this->specialRequest;
    }

    public function setSpecialRequest(?string $specialRequest): self
    {
        $this->specialRequest = $specialRequest;

        return $this;
    }

    public function getStatus(): ReservationStatus
    {
        return $this->status;
    }

    public function setStatus(ReservationStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDiningTable(): ?DiningTable
    {
        return $this->diningTable;
    }

    public function setDiningTable(?DiningTable $diningTable): self
    {
        $this->diningTable = $diningTable;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
