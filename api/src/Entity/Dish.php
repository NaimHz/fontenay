<?php

namespace App\Entity;

use App\Enum\DishCategory;
use App\Repository\DishRepository;
use Doctrine\ORM\Mapping as ORM;

/** Un plat de la carte, rattaché à un établissement. */
#[ORM\Entity(repositoryClass: DishRepository::class)]
class Dish
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Establishment::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Establishment $establishment = null;

    #[ORM\Column(length: 150)]
    private string $name;

    #[ORM\Column(enumType: DishCategory::class)]
    private DishCategory $category;

    /** Prix en euros. */
    #[ORM\Column(type: 'decimal', precision: 6, scale: 2)]
    private string $price = '0.00';

    /** Allergènes connus du plat (information cuisine). */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $allergens = null;

    #[ORM\Column]
    private bool $active = true;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCategory(): DishCategory
    {
        return $this->category;
    }

    public function setCategory(DishCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getAllergens(): ?string
    {
        return $this->allergens;
    }

    public function setAllergens(?string $allergens): self
    {
        $this->allergens = $allergens;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }
}
