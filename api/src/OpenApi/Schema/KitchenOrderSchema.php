<?php

namespace App\OpenApi\Schema;

use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

/** Documentation OpenAPI de la forme renvoyée par ApiNormalizer. */
class KitchenOrderSchema
{
    #[OA\Property(type: 'integer')]
    public int $id;

    #[OA\Property(type: 'integer', nullable: true)]
    public ?int $tableNumber;

    #[OA\Property(type: 'string', format: 'date-time', nullable: true)]
    public ?string $sentAt;

    #[OA\Property(type: 'string', nullable: true)]
    public ?string $allergies;

    #[OA\Property(type: 'array', items: new OA\Items(ref: new Model(type: OrderItemSchema::class)))]
    public array $items;
}
