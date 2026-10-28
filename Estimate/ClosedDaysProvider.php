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

/**
 * The days a carrier does not work on.
 */
interface ClosedDaysProvider
{
    /**
     * @return array<string, true> the closed days between the two bounds, both included, keyed Y-m-d
     */
    public function between(int $moduleId, \DateTimeImmutable $first, \DateTimeImmutable $last): array;
}
