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

namespace DeliveryDelay\Tests\Double;

use DeliveryDelay\Estimate\ClosedDaysProvider;

/**
 * A calendar written in the test: the days given, and the weekends when asked.
 */
final class FakeClosedDays implements ClosedDaysProvider
{
    public int $calls = 0;

    /**
     * @param list<string> $closedDays  Y-m-d
     * @param bool         $closeEveryDay every day of the calendar is closed
     */
    public function __construct(
        private readonly array $closedDays = [],
        private readonly bool $closedWeekends = false,
        private readonly bool $closeEveryDay = false,
    ) {
    }

    public function between(int $moduleId, \DateTimeImmutable $first, \DateTimeImmutable $last): array
    {
        ++$this->calls;

        $closed = [];

        for ($day = $first->setTime(0, 0); $day <= $last; $day = $day->modify('+1 day')->setTime(0, 0)) {
            $key = $day->format('Y-m-d');

            if ($this->closeEveryDay
                || \in_array($key, $this->closedDays, true)
                || ($this->closedWeekends && (int) $day->format('N') >= 6)) {
                $closed[$key] = true;
            }
        }

        return $closed;
    }
}
