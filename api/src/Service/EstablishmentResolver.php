<?php

namespace App\Service;

use App\Entity\Establishment;
use App\Entity\User;
use App\Repository\EstablishmentRepository;

/**
 * Détermine sur quel établissement travaille un membre du personnel :
 * celui auquel il est rattaché, ou — pour le propriétaire (multi-sites) —
 * celui passé en paramètre. Garantit le cloisonnement par établissement.
 */
class EstablishmentResolver
{
    public function __construct(private readonly EstablishmentRepository $establishments)
    {
    }

    public function resolve(User $user, ?int $requestedId): ?Establishment
    {
        if ($user->getEstablishment() !== null) {
            return $user->getEstablishment();
        }

        return $requestedId ? $this->establishments->find($requestedId) : null;
    }
}
