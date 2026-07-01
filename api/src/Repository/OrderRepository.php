<?php

namespace App\Repository;

use App\Entity\DiningTable;
use App\Entity\Establishment;
use App\Entity\Order;
use App\Enum\OrderStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Order> */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /** Commandes envoyées en cuisine et pas encore entièrement servies (fil de la cuisine). */
    public function kitchenFeed(Establishment $establishment): array
    {
        return $this->createQueryBuilder('o')
            ->join('o.diningTable', 't')
            ->andWhere('t.establishment = :establishment')
            ->andWhere('o.status = :sent')
            ->setParameter('establishment', $establishment)
            ->setParameter('sent', OrderStatus::SENT->value)
            ->orderBy('o.sentAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** Commande en cours pour une table (non clôturée), la plus récente. */
    public function activeByTable(DiningTable $table): ?Order
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.diningTable = :table')
            ->andWhere('o.status != :closed')
            ->setParameter('table', $table)
            ->setParameter('closed', OrderStatus::CLOSED->value)
            ->orderBy('o.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
