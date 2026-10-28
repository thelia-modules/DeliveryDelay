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

namespace DeliveryDelay\Estimate;

use DeliveryDelay\Value\CartLine;
use DeliveryDelay\Value\ProductDelayRow;

/**
 * The slowest product of a cart sets the pace of the whole parcel.
 */
final class ProductLeadTime
{
    /**
     * @param list<CartLine>              $lines
     * @param array<int, ProductDelayRow> $rowsByProductId
     */
    public static function slowest(array $lines, array $rowsByProductId): int
    {
        $slowest = 0;

        foreach ($lines as $line) {
            $row = $rowsByProductId[$line->productId] ?? null;

            if (null === $row) {
                continue;
            }

            $slowest = max($slowest, $row->daysFor($line->outOfStock));
        }

        return $slowest;
    }
}
