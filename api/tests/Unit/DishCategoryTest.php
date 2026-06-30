<?php

namespace App\Tests\Unit;

use App\Enum\DishCategory;
use PHPUnit\Framework\TestCase;

class DishCategoryTest extends TestCase
{
    public function testLabelsAreHumanReadable(): void
    {
        self::assertSame('Entrée', DishCategory::ENTREE->label());
        self::assertSame('Plats', DishCategory::PLAT->label());
        self::assertSame('Desserts', DishCategory::DESSERT->label());
    }

    public function testEveryCategoryHasALabel(): void
    {
        foreach (DishCategory::cases() as $category) {
            self::assertNotEmpty($category->label());
        }
    }
}
