<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

/** Documentation OpenAPI de la forme renvoyée par ApiNormalizer. */
class ReservationSchema
{
    #[OA\Property(type: 'integer')]
    public int $id;

    #[OA\Property(type: 'string')]
    public string $customerName;

    #[OA\Property(type: 'integer')]
    public int $partySize;

    #[OA\Property(type: 'string', enum: ['midi', 'soir'])]
    public string $service;

    #[OA\Property(type: 'string', format: 'date')]
    public string $date;

    #[OA\Property(type: 'string', nullable: true)]
    public ?string $allergies;

    #[OA\Property(type: 'string', nullable: true)]
    public ?string $specialRequest;

    #[OA\Property(type: 'string', enum: ['pending', 'seated', 'cancelled', 'waitlist'])]
    public string $status;

    #[OA\Property(
        type: 'object',
        nullable: true,
        properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'number', type: 'integer'),
        ],
    )]
    public ?array $table;
}
