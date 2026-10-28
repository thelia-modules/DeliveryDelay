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
 * What the product page tells: the interval, and the country it is for.
 */
final readonly class ProductPageEstimate
{
    public function __construct(
        public DeliveryInterval $interval,
        public string $countryTitle,
    ) {
    }
}
