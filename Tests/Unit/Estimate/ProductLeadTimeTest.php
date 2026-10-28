<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DeliveryDelay\Tests\Unit\Estimate;

use DeliveryDelay\Estimate\ProductLeadTime;
use DeliveryDelay\Value\CartLine;
use DeliveryDelay\Value\ProductDelayRow;
use PHPUnit\Framework\TestCase;

final class ProductLeadTimeTest extends TestCase
{
    public function testTheSlowestProductOfTheCartSetsThePace(): void
    {
        $rows = [1 => new ProductDelayRow(1, 2, null), 2 => new ProductDelayRow(2, 5, null), 3 => new ProductDelayRow(3, 1, null)];

        self::assertSame(5, ProductLeadTime::slowest([new CartLine(1, false), new CartLine(2, false), new CartLine(3, false)], $rows));
    }

    public function testTheRestockDelayIsAddedOnlyForAVariantOutOfStock(): void
    {
        $rows = [1 => new ProductDelayRow(1, 2, 7)];

        self::assertSame(2, ProductLeadTime::slowest([new CartLine(1, false)], $rows));
        self::assertSame(9, ProductLeadTime::slowest([new CartLine(1, true)], $rows));
    }

    public function testZeroIsAValueAndNullIsNotFilledIn(): void
    {
        $zero = new ProductDelayRow(1, 0, 0);
        $unset = new ProductDelayRow(2, null, null);

        self::assertSame(0, $zero->availabilityDays);
        self::assertNull($unset->availabilityDays);
        self::assertSame(0, $zero->daysFor(true));
        self::assertSame(0, $unset->daysFor(true));
        // A restock delay of zero does not stand in for a missing availability delay, nor the reverse.
        self::assertSame(3, (new ProductDelayRow(3, null, 3))->daysFor(true));
        self::assertSame(0, (new ProductDelayRow(3, null, 3))->daysFor(false));
        self::assertSame(4, (new ProductDelayRow(4, 4, 0))->daysFor(true));
    }

    public function testAProductWithoutDelaysAddsNothing(): void
    {
        self::assertSame(0, ProductLeadTime::slowest([new CartLine(9, true)], []));
        self::assertSame(0, ProductLeadTime::slowest([], []));
    }
}
