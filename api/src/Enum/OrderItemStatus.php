<?php

namespace App\Enum;

/** Suivi d'un plat individuel (côté cuisine). */
enum OrderItemStatus: string
{
    case PENDING = 'pending';            // en attente
    case IN_PREPARATION = 'in_preparation'; // en préparation
    case SERVED = 'served';              // servi
}
