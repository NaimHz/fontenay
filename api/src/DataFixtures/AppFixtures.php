<?php

namespace App\DataFixtures;

use App\Entity\Dish;
use App\Entity\DiningTable;
use App\Entity\Establishment;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Reservation;
use App\Entity\User;
use App\Enum\DishCategory;
use App\Enum\OrderItemStatus;
use App\Enum\OrderStatus;
use App\Enum\ReservationStatus;
use App\Enum\ServiceType;
use App\Enum\TableStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // --- Établissements -------------------------------------------------
        $clos = (new Establishment())
            ->setName('Le Clos Fontenay')
            ->setSlug('clos-fontenay')
            ->setCity('Lyon — Presqu\'île')
            ->setCapacity(52);
        $manager->persist($clos);

        $cellier = (new Establishment())
            ->setName('Le Cellier Fontenay')
            ->setSlug('cellier-fontenay')
            ->setCity('Lyon — Croix-Rousse')
            ->setCapacity(48);
        $manager->persist($cellier);

        // --- Tables ---------------------------------------------------------
        $closTables = $this->createTables($manager, $clos, [2, 2, 4, 4, 2, 6, 4, 2, 4, 4, 2, 8]);
        $this->createTables($manager, $cellier, [2, 4, 2, 4, 6, 2, 4, 2, 4, 4]);

        // --- Comptes personnel (mot de passe de test : "password") ----------
        $this->createUser($manager, 'owner@fontenay.fr', 'Madeleine Fontenay', [User::ROLE_OWNER], null);
        $this->createUser($manager, 'maitre@clos.fr', 'Hugo Berthier', [User::ROLE_MAITRE], $clos);
        $this->createUser($manager, 'serveur@clos.fr', 'Léa Marchand', [User::ROLE_SERVER], $clos);
        $this->createUser($manager, 'cuisine@clos.fr', 'Chef Antoine', [User::ROLE_KITCHEN], $clos);
        $this->createUser($manager, 'maitre@cellier.fr', 'Camille Roux', [User::ROLE_MAITRE], $cellier);
        $this->createUser($manager, 'serveur@cellier.fr', 'Yanis Dubois', [User::ROLE_SERVER], $cellier);

        // --- Carte ----------------------------------------------------------
        $closDishes = $this->createMenu($manager, $clos);
        $this->createMenu($manager, $cellier);

        // --- Réservations du jour (avec allergies) --------------------------
        $today = new \DateTimeImmutable('today');

        // Une réservation déjà installée → table occupée.
        $r1 = $this->createReservation($clos, 'Famille Lemoine', 'lemoine@example.com', '0600000001', $today, ServiceType::SOIR, 4, 'Arachides (sévère)', 'Table près de la fenêtre');
        $r1->setStatus(ReservationStatus::SEATED)->setDiningTable($closTables[2]);
        $closTables[2]->setStatus(TableStatus::OCCUPIED);
        $manager->persist($r1);

        $r2 = $this->createReservation($clos, 'M. et Mme Garnier', 'garnier@example.com', '0600000002', $today, ServiceType::SOIR, 2, 'Sans gluten', null);
        $r2->setDiningTable($closTables[0])->setStatus(ReservationStatus::PENDING);
        $closTables[0]->setStatus(TableStatus::RESERVED);
        $manager->persist($r2);

        $r3 = $this->createReservation($clos, 'Sophie Nguyen', 'nguyen@example.com', '0600000003', $today, ServiceType::SOIR, 6, null, 'Anniversaire');
        $manager->persist($r3);

        $r4 = $this->createReservation($cellier, 'Table Vidal', 'vidal@example.com', '0600000004', $today, ServiceType::SOIR, 3, 'Lactose', null);
        $manager->persist($r4);

        // --- Commande démo déjà envoyée en cuisine (table Lemoine) ----------
        // Permet à l'écran cuisine d'afficher une carte dès le chargement,
        // avec les allergies de la réservation mises en évidence.
        $order = (new Order())
            ->setDiningTable($closTables[2])
            ->setReservation($r1)
            ->setStatus(OrderStatus::SENT)
            ->setSentAt(new \DateTimeImmutable());
        $order->addItem($this->orderLine($closDishes[0], 2, OrderItemStatus::PENDING));        // Tomates Mozza
        $order->addItem($this->orderLine($closDishes[3], 2, OrderItemStatus::IN_PREPARATION)); // Poulet Curry
        $order->addItem($this->orderLine($closDishes[7], 1, OrderItemStatus::PENDING));        // Tarte au citron
        $manager->persist($order);

        $manager->flush();
    }

    /**
     * @param int[] $seatsList
     * @return DiningTable[]
     */
    private function createTables(ObjectManager $manager, Establishment $establishment, array $seatsList): array
    {
        $tables = [];
        foreach ($seatsList as $i => $seats) {
            $table = (new DiningTable())
                ->setEstablishment($establishment)
                ->setNumber(sprintf('%02d', $i + 1))
                ->setSeats($seats)
                ->setStatus(TableStatus::FREE);
            $manager->persist($table);
            $tables[] = $table;
        }

        return $tables;
    }

    /** @param list<string> $roles */
    private function createUser(ObjectManager $manager, string $email, string $fullName, array $roles, ?Establishment $establishment): void
    {
        $user = (new User())
            ->setEmail($email)
            ->setFullName($fullName)
            ->setRoles($roles)
            ->setEstablishment($establishment);
        $user->setPassword($this->hasher->hashPassword($user, 'password'));
        $manager->persist($user);
    }

    /** @return Dish[] */
    private function createMenu(ObjectManager $manager, Establishment $establishment): array
    {
        $created = [];
        $dishes = [
            [DishCategory::ENTREE, 'Tomates Mozza', '12.50', 'Lait'],
            [DishCategory::ENTREE, 'Salade César', '13.00', 'Œuf, Gluten, Poisson (anchois)'],
            [DishCategory::ENTREE, 'Velouté de saison', '11.00', 'Céleri'],
            [DishCategory::PLAT, 'Poulet Curry', '24.00', 'Lait'],
            [DishCategory::PLAT, 'Frites Patates Douces', '18.50', null],
            [DishCategory::PLAT, 'Filet de bar, beurre blanc', '29.00', 'Poisson, Lait'],
            [DishCategory::PLAT, 'Risotto aux cèpes', '22.00', 'Lait'],
            [DishCategory::DESSERT, 'Tarte au citron', '10.50', 'Gluten, Œuf, Lait'],
            [DishCategory::DESSERT, 'Moelleux chocolat', '11.00', 'Gluten, Œuf, Lait'],
            [DishCategory::BOISSON, 'Eau plate 75cl', '5.00', null],
            [DishCategory::BOISSON, 'Verre de Côtes-du-Rhône', '8.00', 'Sulfites'],
            [DishCategory::DIGESTIF, 'Café gourmand', '9.00', 'Gluten, Lait, Œuf'],
        ];

        foreach ($dishes as [$category, $name, $price, $allergens]) {
            $dish = (new Dish())
                ->setEstablishment($establishment)
                ->setCategory($category)
                ->setName($name)
                ->setPrice($price)
                ->setAllergens($allergens);
            $manager->persist($dish);
            $created[] = $dish;
        }

        return $created;
    }

    private function orderLine(Dish $dish, int $quantity, OrderItemStatus $status): OrderItem
    {
        return (new OrderItem())
            ->setDish($dish)
            ->setQuantity($quantity)
            ->setUnitPrice($dish->getPrice())
            ->setStatus($status);
    }

    private function createReservation(
        Establishment $establishment,
        string $name,
        string $email,
        string $phone,
        \DateTimeImmutable $date,
        ServiceType $service,
        int $partySize,
        ?string $allergies,
        ?string $specialRequest,
    ): Reservation {
        return (new Reservation())
            ->setEstablishment($establishment)
            ->setCustomerName($name)
            ->setCustomerEmail($email)
            ->setCustomerPhone($phone)
            ->setDate($date)
            ->setService($service)
            ->setPartySize($partySize)
            ->setAllergies($allergies)
            ->setSpecialRequest($specialRequest);
    }
}
