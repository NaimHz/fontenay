<?php

namespace App\Controller\Api;

use App\Entity\DiningTable;
use App\Entity\Reservation;
use App\Entity\User;
use App\Enum\ReservationStatus;
use App\Enum\TableStatus;
use App\OpenApi\Schema\ReservationSchema;
use App\OpenApi\Schema\TableSchema;
use App\Repository\DiningTableRepository;
use App\Repository\ReservationRepository;
use App\Service\ApiNormalizer;
use App\Service\EstablishmentResolver;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/** Plan de salle, planning des réservations, installation des clients (personnel). */
#[Route('/api')]
#[OA\Tag(name: 'Salle')]
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
    #[OA\Parameter(
        name: 'establishmentId',
        description: "Requis si l'utilisateur est rattaché à plusieurs établissements.",
        in: 'query',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Response(
        response: 200,
        description: 'Toutes les tables de l\'établissement, triées par numéro.',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: TableSchema::class))),
    )]
    #[OA\Response(response: 400, description: 'Établissement non déterminé.')]
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
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(content: new OA\JsonContent(
        required: ['status'],
        properties: [
            new OA\Property(property: 'status', type: 'string', enum: ['free', 'reserved', 'occupied']),
        ],
    ))]
    #[OA\Response(response: 200, description: 'Table mise à jour.', content: new Model(type: TableSchema::class))]
    #[OA\Response(response: 422, description: 'Statut invalide.')]
    public function updateTable(DiningTable $table, Request $request): JsonResponse
    {
        $status = TableStatus::tryFrom((string) (json_decode($request->getContent(), true)['status'] ?? ''));
        if (!$status) {
            return $this->json(['error' => 'Statut invalide.'], 422);
        }

        $table->setStatus($status);
        if ($status === TableStatus::FREE) {
            $table->setServer(null);
        }
        $this->em->flush();

        return $this->json($this->normalizer->table($table));
    }

    /** Planning des réservations du jour (avec allergies). */
    #[Route('/reservations', name: 'api_reservations', methods: ['GET'])]
    #[OA\Parameter(
        name: 'establishmentId',
        description: "Requis si l'utilisateur est rattaché à plusieurs établissements.",
        in: 'query',
        schema: new OA\Schema(type: 'integer'),
    )]
    #[OA\Parameter(
        name: 'date',
        description: 'Vue jour : date (AAAA-MM-JJ). Par défaut : aujourd\'hui.',
        in: 'query',
        schema: new OA\Schema(type: 'string', format: 'date'),
    )]
    #[OA\Parameter(
        name: 'from',
        description: 'Vue semaine : début de plage (AAAA-MM-JJ), à utiliser avec "to".',
        in: 'query',
        schema: new OA\Schema(type: 'string', format: 'date'),
    )]
    #[OA\Parameter(
        name: 'to',
        description: 'Vue semaine : fin de plage (AAAA-MM-JJ), à utiliser avec "from".',
        in: 'query',
        schema: new OA\Schema(type: 'string', format: 'date'),
    )]
    #[OA\Response(
        response: 200,
        description: 'Réservations du jour (ou de la plage from/to).',
        content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: new Model(type: ReservationSchema::class))),
    )]
    #[OA\Response(response: 400, description: 'Établissement non déterminé.')]
    public function reservations(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $establishment = $this->resolver->resolve($user, $request->query->getInt('establishmentId') ?: null);
        if (!$establishment) {
            return $this->json(['error' => 'Établissement non déterminé.'], 400);
        }

        $from = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $request->query->get('from', ''));
        $to = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $request->query->get('to', ''));

        if ($from && $to) {
            // Vue semaine : réservations sur une plage de dates.
            $reservations = $this->reservations->planningBetween($establishment, $from, $to);
        } else {
            // Vue jour : une seule date (ou aujourd'hui par défaut).
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', (string) $request->query->get('date', ''))
                ?: new \DateTimeImmutable('today');
            $reservations = $this->reservations->planning($establishment, $date);
        }

        return $this->json(array_map($this->normalizer->reservation(...), $reservations));
    }

    /** Installe un client : attribue une table et passe la réservation en "installée". */
    #[Route('/reservations/{id}/seat', name: 'api_reservation_seat', methods: ['POST'])]
    #[OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\RequestBody(content: new OA\JsonContent(
        required: ['tableId'],
        properties: [
            new OA\Property(property: 'tableId', type: 'integer'),
        ],
    ))]
    #[OA\Response(response: 200, description: 'Réservation installée.', content: new Model(type: ReservationSchema::class))]
    #[OA\Response(response: 422, description: 'Table invalide (inexistante ou autre établissement).')]
    public function seat(Reservation $reservation, Request $request): JsonResponse
    {
        $tableId = (int) (json_decode($request->getContent(), true)['tableId'] ?? 0);
        $table = $this->tables->find($tableId);
        if (!$table || $table->getEstablishment() !== $reservation->getEstablishment()) {
            return $this->json(['error' => 'Table invalide.'], 422);
        }

        /** @var User $user */
        $user = $this->getUser();

        $reservation->setDiningTable($table)->setStatus(ReservationStatus::SEATED);
        $table->setStatus(TableStatus::OCCUPIED)->setServer($user);
        $this->em->flush();

        return $this->json($this->normalizer->reservation($reservation));
    }
}
