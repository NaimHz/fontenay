<?php

namespace App\Service;

use App\Entity\DiningTable;
use App\Entity\Dish;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Reservation;

/** Met en forme les entités en tableaux JSON, de façon cohérente entre les endpoints. */
class ApiNormalizer
{
    public function table(DiningTable $t): array
    {
        return [
            'id' => $t->getId(),
            'number' => $t->getNumber(),
            'seats' => $t->getSeats(),
            'status' => $t->getStatus()->value,
            'server' => $t->getServer()?->getFullName(),
        ];
    }

    public function reservation(Reservation $r): array
    {
        return [
            'id' => $r->getId(),
            'customerName' => $r->getCustomerName(),
            'partySize' => $r->getPartySize(),
            'service' => $r->getService()->value,
            'date' => $r->getDate()->format('Y-m-d'),
            'allergies' => $r->getAllergies(),
            'specialRequest' => $r->getSpecialRequest(),
            'status' => $r->getStatus()->value,
            'table' => $r->getDiningTable() ? [
                'id' => $r->getDiningTable()->getId(),
                'number' => $r->getDiningTable()->getNumber(),
            ] : null,
        ];
    }

    public function dish(Dish $d): array
    {
        return [
            'id' => $d->getId(),
            'name' => $d->getName(),
            'category' => $d->getCategory()->value,
            'price' => $d->getPrice(),
            'allergens' => $d->getAllergens(),
        ];
    }

    public function orderItem(OrderItem $i): array
    {
        return [
            'id' => $i->getId(),
            'dishId' => $i->getDish()?->getId(),
            'name' => $i->getDish()?->getName(),
            'category' => $i->getDish()?->getCategory()->value,
            'quantity' => $i->getQuantity(),
            'seatNumber' => $i->getSeatNumber(),
            'status' => $i->getStatus()->value,
            'unitPrice' => $i->getUnitPrice(),
        ];
    }

    public function order(Order $o): array
    {
        return [
            'id' => $o->getId(),
            'tableId' => $o->getDiningTable()?->getId(),
            'tableNumber' => $o->getDiningTable()?->getNumber(),
            'status' => $o->getStatus()->value,
            'total' => $o->getTotal(),
            'createdAt' => $o->getCreatedAt()->format(\DateTimeInterface::ATOM),
            'sentAt' => $o->getSentAt()?->format(\DateTimeInterface::ATOM),
            'items' => array_map($this->orderItem(...), $o->getItems()->toArray()),
        ];
    }

    /** Vue cuisine : la table, l'heure d'envoi, les allergies (issues de la réservation) et les plats. */
    public function kitchenOrder(Order $o): array
    {
        return [
            'id' => $o->getId(),
            'tableNumber' => $o->getDiningTable()?->getNumber(),
            'sentAt' => $o->getSentAt()?->format(\DateTimeInterface::ATOM),
            'allergies' => $o->getReservation()?->getAllergies(),
            'items' => array_map($this->orderItem(...), $o->getItems()->toArray()),
        ];
    }
}
