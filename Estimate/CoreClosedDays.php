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

use Thelia\Domain\Shipping\DeliveryDate\Service\DeliveryClosedDays;

/**
 * The calendar of the shop, which is the only one: weekdays, closures of the shop and of the
 * carrier are read through the core. A period asked twice in a request is read once.
 */
final class CoreClosedDays implements ClosedDaysProvider
{
    /** @var array<string, array<string, true>> */
    private array $memo = [];

    public function __construct(private readonly DeliveryClosedDays $closedDays)
    {
    }

    public function between(int $moduleId, \DateTimeImmutable $first, \DateTimeImmutable $last): array
    {
        $key = $moduleId.'|'.$first->format('Y-m-d').'|'.$last->format('Y-m-d');

        return $this->memo[$key] ??= $this->closedDays->between($moduleId, $first, $last);
    }

    public function reset(): void
    {
        $this->memo = [];
    }
}
