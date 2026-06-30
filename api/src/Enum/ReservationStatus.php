<?php

namespace App\Enum;

/** Cycle de vie d'une réservation. */
enum ReservationStatus: string
{
    case PENDING = 'pending';     // enregistrée, à venir
    case SEATED = 'seated';       // client installé à table
    case CANCELLED = 'cancelled'; // annulée
    case WAITLIST = 'waitlist';   // liste d'attente (service complet)
}
