<?php

namespace App\Enum;

/** Catégories de la carte (onglets de la prise de commande). */
enum DishCategory: string
{
    case ENTREE = 'entree';
    case PLAT = 'plat';
    case DESSERT = 'dessert';
    case BOISSON = 'boisson';
    case DIGESTIF = 'digestif';

    public function label(): string
    {
        return match ($this) {
            self::ENTREE => 'Entrée',
            self::PLAT => 'Plats',
            self::DESSERT => 'Desserts',
            self::BOISSON => 'Boissons',
            self::DIGESTIF => 'Digestifs',
        };
    }
}
