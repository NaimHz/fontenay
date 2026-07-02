<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

/** Documentation OpenAPI de la forme renvoyée par ApiNormalizer. */
class DishSchema
{
    #[OA\Property(type: 'integer')]
    public int $id;

    #[OA\Property(type: 'string')]
    public string $name;

    #[OA\Property(type: 'string', enum: ['entree', 'plat', 'dessert', 'boisson', 'digestif'])]
    public string $category;

    #[OA\Property(type: 'number', format: 'float')]
    public float $price;

    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'), nullable: true)]
    public ?array $allergens;
}
