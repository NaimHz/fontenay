<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

/** Documentation OpenAPI de la forme renvoyée par ApiNormalizer. */
class EstablishmentSchema
{
    #[OA\Property(type: 'integer')]
    public int $id;

    #[OA\Property(type: 'string')]
    public string $name;

    #[OA\Property(type: 'string')]
    public string $slug;

    #[OA\Property(type: 'string')]
    public string $city;

    #[OA\Property(type: 'integer')]
    public int $capacity;
}
