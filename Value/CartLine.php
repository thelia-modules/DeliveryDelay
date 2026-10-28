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
 * What the calculation needs to know of one line of a cart: the product, and whether the
 * variant that was picked is out of stock.
 */
final readonly class CartLine
{
    public function __construct(
        public int $productId,
        public bool $outOfStock,
    ) {
    }
}
