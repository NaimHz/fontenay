<?php

namespace App\Entity;

use App\Enum\TableStatus;
use App\Repository\DiningTableRepository;
use Doctrine\ORM\Mapping as ORM;

/** Une table physique de la salle, avec son état courant (code couleur). */
#[ORM\Entity(repositoryClass: DiningTableRepository::class)]
class DiningTable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Establishment::class, inversedBy: 'tables')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Establishment $establishment = null;

    /** Numéro affiché sur le plan de salle (ex. "01"). */
    #[ORM\Column(length: 10)]
    private string $number;

    /** Nombre de couverts de la table. */
    #[ORM\Column]
    private int $seats = 2;

    #[ORM\Column(enumType: TableStatus::class)]
    private TableStatus $status = TableStatus::FREE;

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

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getSeats(): int
    {
        return $this->seats;
    }

    public function setSeats(int $seats): self
    {
        $this->seats = $seats;

        return $this;
    }

    public function getStatus(): TableStatus
    {
        return $this->status;
    }

    public function setStatus(TableStatus $status): self
    {
        $this->status = $status;

        return $this;
    }
}
