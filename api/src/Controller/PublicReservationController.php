<?php

namespace App\Controller;

namespace App\Controller;

use App\Entity\Reservation;
use App\Enum\ReservationStatus;
use App\Enum\ServiceType;
use App\Repository\EstablishmentRepository;
use App\Repository\ReservationRepository;
use App\Service\AvailabilityChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * API publique du site de réservation (accessible sans authentification).
 * Vérifie la disponibilité en temps réel, refuse les doublons, gère la
 * liste d'attente quand le service est complet.
 */
#[Route('/api/public')]
class PublicReservationController extends AbstractController
{
    public function __construct(
        private readonly EstablishmentRepository $establishments,
        private readonly ReservationRepository $reservations,
        private readonly AvailabilityChecker $availability,
        private readonly EntityManagerInterface $em,
    ) {}

    /** Liste des établissements (pour le sélecteur du formulaire). */
    #[Route('/establishments', name: 'public_establishments', methods: ['GET'])]
    public function establishments(): JsonResponse
    {
        $data = array_map(static fn($e) => [
            'id' => $e->getId(),
            'name' => $e->getName(),
            'slug' => $e->getSlug(),
            'city' => $e->getCity(),
            'capacity' => $e->getCapacity(),
        ], $this->establishments->findAll());

        return $this->json($data);
    }

    /** Disponibilité en temps réel pour un établissement / date / service / nb de couverts. */
    #[Route('/availability', name: 'public_availability', methods: ['GET'])]
    public function availability(Request $request): JsonResponse
    {
        $establishment = $this->establishments->find((int) $request->query->get('establishmentId'));
        $service = ServiceType::tryFrom((string) $request->query->get('service'));
        $date = $this->parseDate((string) $request->query->get('date'));
        $partySize = (int) $request->query->get('partySize', 0);

        if (!$establishment || !$service || !$date || $partySize < 1) {
            return $this->json(['error' => 'Paramètres invalides.'], 422);
        }

        $booked = $this->reservations->bookedCovers($establishment, $date, $service);

        return $this->json([
            'available' => $this->availability->isAvailable($establishment->getCapacity(), $booked, $partySize),
            'capacity' => $establishment->getCapacity(),
            'booked' => $booked,
            'remaining' => $this->availability->remaining($establishment->getCapacity(), $booked),
        ]);
    }

    /** Création d'une réservation. Bascule en liste d'attente si le service est complet. */
    #[Route('/reservations', name: 'public_reservation_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];

        $establishment = $this->establishments->find((int) ($payload['establishmentId'] ?? 0));
        $service = ServiceType::tryFrom((string) ($payload['service'] ?? ''));
        $date = $this->parseDate((string) ($payload['date'] ?? ''));
        $partySize = (int) ($payload['partySize'] ?? 0);
        $name = trim((string) ($payload['customerName'] ?? ''));
        $email = trim((string) ($payload['customerEmail'] ?? ''));
        $phone = trim((string) ($payload['customerPhone'] ?? ''));

        $errors = [];
        if (!$establishment) {
            $errors['establishmentId'] = 'Établissement inconnu.';
        }
        if (!$service) {
            $errors['service'] = 'Service invalide (midi ou soir).';
        }
        if (!$date) {
            $errors['date'] = 'Date invalide (format AAAA-MM-JJ).';
        }
        if ($partySize < 1) {
            $errors['partySize'] = 'Nombre de couverts invalide.';
        }
        if ($name === '') {
            $errors['customerName'] = 'Nom requis.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['customerEmail'] = 'Email invalide.';
        }
        if ($phone === '') {
            $errors['customerPhone'] = 'Téléphone requis.';
        }
        if ($errors) {
            return $this->json(['errors' => $errors], 422);
        }

        if ($this->reservations->hasDuplicate($establishment, $email, $date, $service)) {
            return $this->json(['error' => 'Une réservation existe déjà pour ce service.'], 409);
        }

        $booked = $this->reservations->bookedCovers($establishment, $date, $service);
        $waitlisted = !$this->availability->isAvailable($establishment->getCapacity(), $booked, $partySize);

        $reservation = (new Reservation())
            ->setEstablishment($establishment)
            ->setCustomerName($name)
            ->setCustomerEmail($email)
            ->setCustomerPhone($phone)
            ->setDate($date)
            ->setService($service)
            ->setPartySize($partySize)
            ->setAllergies(($payload['allergies'] ?? null) ?: null)
            ->setSpecialRequest(($payload['specialRequest'] ?? null) ?: null)
            ->setStatus($waitlisted ? ReservationStatus::WAITLIST : ReservationStatus::PENDING);

        $this->em->persist($reservation);
        $this->em->flush();

        return $this->json([
            'id' => $reservation->getId(),
            'status' => $reservation->getStatus()->value,
            'waitlisted' => $waitlisted,
            'establishment' => $establishment->getName(),
            'date' => $date->format('Y-m-d'),
            'service' => $service->value,
            'partySize' => $partySize,
        ], 201);
    }

    private function parseDate(string $value): ?\DateTimeImmutable
    {
        if ($value === '') {
            return null;
        }
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date ?: null;
    }
}
