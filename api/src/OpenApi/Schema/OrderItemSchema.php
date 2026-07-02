<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

/** Documentation OpenAPI de la forme renvoyée par ApiNormalizer. */
class OrderItemSchema
{
    #[OA\Property(type: 'integer')]
    public int $id;

    #[OA\Property(type: 'integer', nullable: true)]
    public ?int $dishId;

    #[OA\Property(type: 'string', nullable: true)]
    public ?string $name;

    #[OA\Property(type: 'string', enum: ['entree', 'plat', 'dessert', 'boisson', 'digestif'], nullable: true)]
    public ?string $category;

    #[OA\Property(type: 'integer')]
    public int $quantity;

    #[OA\Property(type: 'integer', nullable: true)]
    public ?int $seatNumber;

    #[OA\Property(type: 'string', enum: ['pending', 'in_preparation', 'served'])]
    public string $status;

    #[OA\Property(type: 'number', format: 'float')]
    public float $unitPrice;
}
