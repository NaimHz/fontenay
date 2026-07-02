<?php

namespace App\OpenApi\Schema;

use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

/** Documentation OpenAPI de la forme renvoyée par ApiNormalizer. */
class OrderSchema
{
    #[OA\Property(type: 'integer')]
    public int $id;

    #[OA\Property(type: 'integer', nullable: true)]
    public ?int $tableId;

    #[OA\Property(type: 'integer', nullable: true)]
    public ?int $tableNumber;

    #[OA\Property(type: 'string', enum: ['open', 'sent', 'served', 'closed'])]
    public string $status;

    #[OA\Property(type: 'number', format: 'float')]
    public float $total;

    #[OA\Property(type: 'string', format: 'date-time')]
    public string $createdAt;

    #[OA\Property(type: 'string', format: 'date-time', nullable: true)]
    public ?string $sentAt;

    #[OA\Property(type: 'array', items: new OA\Items(ref: new Model(type: OrderItemSchema::class)))]
    public array $items;
}
