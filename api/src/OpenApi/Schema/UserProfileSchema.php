<?php

namespace App\OpenApi\Schema;

use OpenApi\Attributes as OA;

/** Documentation OpenAPI de la forme renvoyée par ApiNormalizer. */
class UserProfileSchema
{
    #[OA\Property(type: 'integer')]
    public int $id;

    #[OA\Property(type: 'string', format: 'email')]
    public string $email;

    #[OA\Property(type: 'string')]
    public string $fullName;

    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    public array $roles;

    #[OA\Property(
        type: 'object',
        nullable: true,
        properties: [
            new OA\Property(property: 'id', type: 'integer'),
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'slug', type: 'string'),
        ],
    )]
    public ?array $establishment;
}
