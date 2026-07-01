<?php

namespace App\Controller\Api;

use App\Entity\DiningTable;
use App\Entity\Reservation;
use App\Entity\User;
use App\Enum\ReservationStatus;
use App\Enum\TableStatus;
use App\Repository\DiningTableRepository;
use App\Repository\ReservationRepository;
use App\Service\ApiNormalizer;
use App\Service\EstablishmentResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/** Plan de salle, planning des réservations, installation des clients (personnel). */
#[Route('/api')]
class SalleController extends AbstractController
{
    public function __construct(
        private readonly DiningTableRepository $tables,
        private readonly ReservationRepository $reservations,
        private readonly EstablishmentResolver $resolver,
        private readonly ApiNormalizer $normalizer,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /** Plan de salle : toutes les tables et leur état. */
    #[Route('/tables', name: 'api_tables', methods: ['GET'])]
    public function tables(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $establishment = $this->resolver->resolve($user, $request->query->getInt('establishmentId') ?: null);
        if (!$establishment) {
            return $this->json(['error' => 'Établissement non déterminé.'], 400);
        }

        $tables = $this->tables->findBy(['establishment' => $establishment], ['number' => 'ASC']);

        return $this->json(array_map($this->normalizer->table(...), $tables));
    }

    /** Change l'état d'une table (libre / réservée / occupée). */
    #[Route('/tables/{id}', name: 'api_table_update', methods: ['PATCH'])]
    public function updateTable(DiningTable $table, Request $request): JsonResponse
    {
        $status = TableStatus::tryFrom((string) (json_decode($request->getContent(), true)['status'] ?? ''));
        if (!$status) {
            return $this->json(['error' => 'Statut invalide.'], 422);
        }

        $table->setStatus($status);
        $this->em->flush();

        return $this->json($this->normalizer->table($table));
    }

    /** Planning des réservations du jour (avec allergies). */
    #[Route('/reservations', name: 'api_reservations', methods: ['GET'])]
    public function reservations(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $establishment = $this->resolver->resolve($user, $request->query->getInt('establishmentId') ?: null);
        if (!$establishment) {
            return $this->json(['error' => 'Établissement non déterminé.'], 400);
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $request->query->get('date', ''))
            ?: new \DateTimeImmutable('today');

        $reservations = $this->reservations->planning($establishment, $date);

        return $this->json(array_map($this->normalizer->reservation(...), $reservations));
    }

    /** Installe un client : attribue une table et passe la réservation en "installée". */
    #[Route('/reservations/{id}/seat', name: 'api_reservation_seat', methods: ['POST'])]
    public function seat(Reservation $reservation, Request $request): JsonResponse
    {
        $tableId = (int) (json_decode($request->getContent(), true)['tableId'] ?? 0);
        $table = $this->tables->find($tableId);
        if (!$table || $table->getEstablishment() !== $reservation->getEstablishment()) {
            return $this->json(['error' => 'Table invalide.'], 422);
        }

        $reservation->setDiningTable($table)->setStatus(ReservationStatus::SEATED);
        $table->setStatus(TableStatus::OCCUPIED);
        $this->em->flush();

        return $this->json($this->normalizer->reservation($reservation));
    }
}
