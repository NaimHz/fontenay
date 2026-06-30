<?php

namespace App\Enum;

/** Cycle de vie d'une commande de table. */
enum OrderStatus: string
{
    case OPEN = 'open';     // en cours de saisie par le serveur
    case SENT = 'sent';     // envoyée en cuisine
    case SERVED = 'served'; // tous les plats servis
    case CLOSED = 'closed'; // table clôturée (addition demandée)
}
