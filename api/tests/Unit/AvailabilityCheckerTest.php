<?php

namespace App\Tests\Unit;

use App\Service\AvailabilityChecker;
use PHPUnit\Framework\TestCase;

class AvailabilityCheckerTest extends TestCase
{
    private AvailabilityChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new AvailabilityChecker();
    }

    public function testAvailableWhenEnoughSeats(): void
    {
        self::assertTrue($this->checker->isAvailable(52, 40, 4));
    }

    public function testUnavailableWhenServiceIsFull(): void
    {
        self::assertFalse($this->checker->isAvailable(52, 50, 4));
    }

    public function testRemainingNeverGoesNegative(): void
    {
        self::assertSame(0, $this->checker->remaining(52, 60));
    }

    public function testZeroPartySizeIsNeverAvailable(): void
    {
        self::assertFalse($this->checker->isAvailable(52, 0, 0));
    }
}
