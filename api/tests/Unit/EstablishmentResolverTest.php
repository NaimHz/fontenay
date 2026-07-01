<?php

namespace App\Tests\Unit;

use App\Entity\Establishment;
use App\Entity\User;
use App\Repository\EstablishmentRepository;
use App\Service\EstablishmentResolver;
use PHPUnit\Framework\TestCase;

class EstablishmentResolverTest extends TestCase
{
    /** Faux repository (sans base) : renvoie toujours l'établissement fourni. */
    private function repositoryReturning(?Establishment $establishment): EstablishmentRepository
    {
        return new class($establishment) extends EstablishmentRepository {
            public function __construct(private readonly ?Establishment $establishment)
            {
            }

            public function find($id, $lockMode = null, $lockVersion = null): ?object
            {
                return $this->establishment;
            }
        };
    }

    public function testUsesTheUsersOwnEstablishment(): void
    {
        $own = new Establishment();
        $user = (new User())->setEstablishment($own);

        self::assertSame($own, (new EstablishmentResolver($this->repositoryReturning(null)))->resolve($user, 999));
    }

    public function testFallsBackToRequestedEstablishmentForOwner(): void
    {
        $requested = new Establishment();
        $owner = new User(); // pas d'établissement = propriétaire multi-sites

        self::assertSame($requested, (new EstablishmentResolver($this->repositoryReturning($requested)))->resolve($owner, 2));
    }

    public function testReturnsNullWhenOwnerGivesNoEstablishment(): void
    {
        self::assertNull((new EstablishmentResolver($this->repositoryReturning(null)))->resolve(new User(), null));
    }
}
