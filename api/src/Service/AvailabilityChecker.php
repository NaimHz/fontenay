<?php

namespace App\Service;

/**
 * Logique de disponibilité d'un service, isolée et pure (donc testable) :
 * un service accepte une réservation tant que les couverts déjà réservés
 * plus la nouvelle demande ne dépassent pas la capacité de l'établissement.
 */
class AvailabilityChecker
{
    public function remaining(int $capacity, int $booked): int
    {
        return max(0, $capacity - $booked);
    }

    public function isAvailable(int $capacity, int $booked, int $partySize): bool
    {
        return $partySize > 0 && $this->remaining($capacity, $booked) >= $partySize;
    }
}
