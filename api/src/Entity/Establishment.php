<?php

namespace App\Entity;

use App\Repository\EstablishmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Un établissement Fontenay. Toutes les données métier y sont rattachées,
 * ce qui prépare l'arrivée d'un troisième site sans refonte (cloisonnement).
 */
#[ORM\Entity(repositoryClass: EstablishmentRepository::class)]
class Establishment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $name;

    #[ORM\Column(length: 120, unique: true)]
    private string $slug;

    #[ORM\Column(length: 120)]
    private string $city;

    /** Nombre de couverts de la maison. */
    #[ORM\Column]
    private int $capacity = 0;

    /** @var Collection<int, DiningTable> */
    #[ORM\OneToMany(targetEntity: DiningTable::class, mappedBy: 'establishment', cascade: ['persist'])]
    private Collection $tables;

    public function __construct()
    {
        $this->tables = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): self
    {
        $this->capacity = $capacity;

        return $this;
    }

    /** @return Collection<int, DiningTable> */
    public function getTables(): Collection
    {
        return $this->tables;
    }

    public function addTable(DiningTable $table): self
    {
        if (!$this->tables->contains($table)) {
            $this->tables->add($table);
            $table->setEstablishment($this);
        }

        return $this;
    }
}
