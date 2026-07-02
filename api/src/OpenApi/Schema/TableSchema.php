<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

/** Documentation OpenAPI de la forme renvoyée par ApiNormalizer. */
class TableSchema
{
    #[OA\Property(type: 'integer')]
    public int $id;

    #[OA\Property(type: 'integer')]
    public int $number;

    #[OA\Property(type: 'integer')]
    public int $seats;

    #[OA\Property(type: 'string', enum: ['free', 'reserved', 'occupied'])]
    public string $status;
}
