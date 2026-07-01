<?php

namespace App\Repository;

use App\Entity\DiningTable;
use App\Entity\Establishment;
use App\Enum\ReservationStatus;
use App\Enum\ServiceType;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Reservation> */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /** Couverts déjà réservés pour un service (hors annulations et liste d'attente). */
    public function bookedCovers(Establishment $establishment, \DateTimeImmutable $date, ServiceType $service): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COALESCE(SUM(r.partySize), 0)')
            ->andWhere('r.establishment = :establishment')
            ->andWhere('r.date = :date')
            ->andWhere('r.service = :service')
            ->andWhere('r.status NOT IN (:excluded)')
            ->setParameter('establishment', $establishment)
            ->setParameter('date', $date)
            ->setParameter('service', $service->value)
            ->setParameter('excluded', [ReservationStatus::CANCELLED->value, ReservationStatus::WAITLIST->value])
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** Détecte un doublon (même client, même service) pour éviter les réservations en double. */
    public function hasDuplicate(Establishment $establishment, string $email, \DateTimeImmutable $date, ServiceType $service): bool
    {
        $count = (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.establishment = :establishment')
            ->andWhere('r.customerEmail = :email')
            ->andWhere('r.date = :date')
            ->andWhere('r.service = :service')
            ->andWhere('r.status != :cancelled')
            ->setParameter('establishment', $establishment)
            ->setParameter('email', $email)
            ->setParameter('date', $date)
            ->setParameter('service', $service->value)
            ->setParameter('cancelled', ReservationStatus::CANCELLED->value)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /** Réservations d'un service (planning) pour un établissement et une date. */
    public function planning(Establishment $establishment, \DateTimeImmutable $date): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.establishment = :establishment')
            ->andWhere('r.date = :date')
            ->andWhere('r.status != :cancelled')
            ->setParameter('establishment', $establishment)
            ->setParameter('date', $date)
            ->setParameter('cancelled', ReservationStatus::CANCELLED->value)
            ->orderBy('r.service', 'ASC')
            ->addOrderBy('r.customerName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** Réservation actuellement installée à une table (pour rattacher les allergies à la commande). */
    public function activeForTable(DiningTable $table): ?Reservation
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.diningTable = :table')
            ->andWhere('r.status = :seated')
            ->setParameter('table', $table)
            ->setParameter('seated', ReservationStatus::SEATED->value)
            ->orderBy('r.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
