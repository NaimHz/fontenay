<?php

namespace App\Tests\Unit;

use App\Entity\Order;
use App\Entity\OrderItem;
use PHPUnit\Framework\TestCase;

class OrderTotalTest extends TestCase
{
    public function testTotalSumsLines(): void
    {
        $order = new Order();
        $order->addItem((new OrderItem())->setUnitPrice('12.50')->setQuantity(2)); // 25.00
        $order->addItem((new OrderItem())->setUnitPrice('8.00')->setQuantity(3));  // 24.00

        self::assertSame('49.00', $order->getTotal());
    }

    public function testEmptyOrderTotalsZero(): void
    {
        self::assertSame('0.00', (new Order())->getTotal());
    }
}
