<?php

namespace App\Enum;

/** État d'une table en salle (code couleur repris partout : vert / rouge / or). */
enum TableStatus: string
{
    case FREE = 'free';        // libre (vert)
    case RESERVED = 'reserved'; // réservée (rouge)
    case OCCUPIED = 'occupied'; // occupée (rouge)
}
