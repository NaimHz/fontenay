<?php

namespace App\Tests\Unit;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserRolesTest extends TestCase
{
    public function testEveryUserIsAtLeastAuthenticated(): void
    {
        $user = new User();
        self::assertContains('ROLE_USER', $user->getRoles());
    }

    public function testRolesAreNotDuplicated(): void
    {
        $user = (new User())->setRoles([User::ROLE_SERVER, User::ROLE_SERVER]);
        self::assertSame([User::ROLE_SERVER, 'ROLE_USER'], $user->getRoles());
    }

    public function testIdentifierIsEmail(): void
    {
        $user = (new User())->setEmail('serveur@clos.fr');
        self::assertSame('serveur@clos.fr', $user->getUserIdentifier());
    }
}
