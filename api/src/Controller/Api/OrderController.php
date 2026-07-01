<?php

namespace App\Controller\Api;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Enum\OrderItemStatus;
use App\Enum\OrderStatus;
use App\Enum\TableStatus;
use App\Repository\DiningTableRepository;
use App\Repository\DishRepository;
use App\Repository\OrderRepository;
use App\Repository\ReservationRepository;
use App\Service\ApiNormalizer;
use App\Service\EstablishmentResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/** Prise de commande, transmission en cuisine, suivi des plats et clôture de table. */
#[Route('/api')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository $orders,
        private readonly DiningTableRepository $tables,
        private readonly DishRepository $dishes,
        private readonly ReservationRepository $reservations,
        private readonly EstablishmentResolver $resolver,
        private readonly ApiNormalizer $normalizer,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /** Prend une commande à une table et l'envoie directement en cuisine. */
    #[Route('/orders', name: 'api_order_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $payload = json_decode($request->getContent(), true) ?? [];

        $table = $this->tables->find((int) ($payload['tableId'] ?? 0));
        if (!$table) {
            return $this->json(['error' => 'Table inconnue.'], 422);
        }

        $order = (new Order())
            ->setDiningTable($table)
            ->setServer($user)
            ->setReservation($this->reservations->activeForTable($table))
            ->setStatus(OrderStatus::SENT)
            ->setSentAt(new \DateTimeImmutable());

        foreach ($payload['items'] ?? [] as $line) {
            $dish = $this->dishes->find((int) ($line['dishId'] ?? 0));
            $quantity = (int) ($line['quantity'] ?? 0);
            if (!$dish || $quantity < 1) {
                continue;
            }
            $order->addItem(
                (new OrderItem())
                    ->setDish($dish)
                    ->setQuantity($quantity)
                    ->setSeatNumber(isset($line['seatNumber']) ? (int) $line['seatNumber'] : null)
                    ->setUnitPrice($dish->getPrice())
                    ->setStatus(OrderItemStatus::PENDING),
            );
        }

        if ($order->getItems()->isEmpty()) {
            return $this->json(['error' => 'Aucun plat valide dans la commande.'], 422);
        }

        $table->setStatus(TableStatus::OCCUPIED);
        $this->em->persist($order);
        $this->em->flush();

        return $this->json($this->normalizer->order($order), 201);
    }

    /** Commande en cours d'une table (pour l'afficher / demander l'addition). */
    #[Route('/orders', name: 'api_order_active', methods: ['GET'])]
    public function active(Request $request): JsonResponse
    {
        $table = $this->tables->find($request->query->getInt('tableId'));
        if (!$table) {
            return $this->json(['error' => 'Table inconnue.'], 422);
        }

        $order = $this->orders->activeByTable($table);

        return $this->json($order ? $this->normalizer->order($order) : null);
    }

    /** Clôture la table (addition demandée) et la libère. */
    #[Route('/orders/{id}/close', name: 'api_order_close', methods: ['POST'])]
    public function close(Order $order): JsonResponse
    {
        $order->setStatus(OrderStatus::CLOSED);
        $order->getDiningTable()?->setStatus(TableStatus::FREE);
        $this->em->flush();

        return $this->json($this->normalizer->order($order));
    }

    /** Fil de la cuisine : commandes entrantes en temps réel (polling), allergies en évidence. */
    #[Route('/kitchen/orders', name: 'api_kitchen_orders', methods: ['GET'])]
    public function kitchen(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $establishment = $this->resolver->resolve($user, $request->query->getInt('establishmentId') ?: null);
        if (!$establishment) {
            return $this->json(['error' => 'Établissement non déterminé.'], 400);
        }

        $orders = $this->orders->kitchenFeed($establishment);

        return $this->json(array_map($this->normalizer->kitchenOrder(...), $orders));
    }

    /** Validation d'un plat côté cuisine (en préparation / servi). */
    #[Route('/order-items/{id}', name: 'api_order_item_update', methods: ['PATCH'])]
    public function updateItem(OrderItem $item, Request $request): JsonResponse
    {
        $status = OrderItemStatus::tryFrom((string) (json_decode($request->getContent(), true)['status'] ?? ''));
        if (!$status) {
            return $this->json(['error' => 'Statut invalide.'], 422);
        }

        $item->setStatus($status);

        // Quand tous les plats sont servis, la commande sort du fil de la cuisine.
        $order = $item->getOrder();
        if ($order !== null) {
            $allServed = true;
            foreach ($order->getItems() as $line) {
                if ($line->getStatus() !== OrderItemStatus::SERVED) {
                    $allServed = false;
                    break;
                }
            }
            if ($allServed) {
                $order->setStatus(OrderStatus::SERVED);
            }
        }

        $this->em->flush();

        return $this->json($this->normalizer->orderItem($item));
    }
}
