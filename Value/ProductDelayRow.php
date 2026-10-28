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

namespace DeliveryDelay\Value;

/**
 * The delays of one product. Null is "not filled in", 0 is a value: a product made to order
 * on the spot is not a product nobody described.
 */
final readonly class ProductDelayRow
{
    public function __construct(
        public int $productId,
        public ?int $availabilityDays,
        public ?int $restockDays,
    ) {
    }

    /**
     * The days this product adds to the preparation: its availability always, its restock delay
     * on top when the variant ordered is out of stock.
     */
    public function daysFor(bool $outOfStock): int
    {
        return ($this->availabilityDays ?? 0) + ($outOfStock ? ($this->restockDays ?? 0) : 0);
    }
}
