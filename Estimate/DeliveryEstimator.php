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

use DeliveryDelay\Value\CarrierDelays;
use DeliveryDelay\Value\DeliveryInterval;

/**
 * When a parcel ordered at a given moment arrives, counted in the days the carrier works.
 *
 * It holds no state and reads neither session nor request: everything it needs comes in the
 * arguments, which is why it answers in a console command as well as in a page.
 *
 * The first working day is the first day of preparation. With one day of preparation the
 * parcel leaves on that very day; with three, two working days later. Transit then counts from
 * the day it leaves.
 */
final readonly class DeliveryEstimator
{
    private const FIRST_WINDOW_DAYS = 120;

    /** A calendar closed for this long has no working day: the answer is "unknown". */
    private const LAST_WINDOW_DAYS = 960;

    public function __construct(private ClosedDaysProvider $closedDays)
    {
    }

    /**
     * @param int                $productDays the days the slowest product of the order adds to the preparation
     * @param \DateTimeImmutable $now         in the time zone of the shop
     */
    public function estimate(CarrierDelays $delays, int $productDays, \DateTimeImmutable $now): ?DeliveryInterval
    {
        $today = $now->setTime(0, 0);

        // The calendar is read once for a whole window, and a longer one only when the count
        // runs off the end of this one.
        for ($window = self::FIRST_WINDOW_DAYS; $window <= self::LAST_WINDOW_DAYS; $window *= 2) {
            $last = $today->modify('+'.$window.' days');
            $closed = $this->closedDays->between($delays->moduleId, $today, $last);

            $interval = $this->count($delays, $productDays, $now, $today, $last, $closed);

            if (null !== $interval) {
                return $interval;
            }
        }

        return null;
    }

    /**
     * @param array<string, true> $closed
     */
    private function count(
        CarrierDelays $delays,
        int $productDays,
        \DateTimeImmutable $now,
        \DateTimeImmutable $today,
        \DateTimeImmutable $last,
        array $closed,
    ): ?DeliveryInterval {
        $secondOfNow = (int) $now->format('G') * 3600 + (int) $now->format('i') * 60 + (int) $now->format('s');
        $beforeCutoff = null === $delays->cutoffSecond || $secondOfNow < $delays->cutoffSecond;

        $firstWorkingDay = isset($closed[$today->format('Y-m-d')]) || !$beforeCutoff
            ? $this->nextOpenDay($today, $last, $closed)
            : $today;

        $preparation = max(0, $delays->preparationDays + $productDays);
        $departure = $this->addOpenDays($firstWorkingDay, max($preparation - 1, 0), $last, $closed);
        $earliest = $this->addOpenDays($departure, $delays->transitMinDays, $last, $closed);
        $latest = $this->addOpenDays($departure, $delays->transitMaxDays, $last, $closed);

        if (null === $earliest || null === $latest) {
            return null;
        }

        return new DeliveryInterval($earliest, $latest);
    }

    /**
     * @param array<string, true> $closed
     */
    private function addOpenDays(?\DateTimeImmutable $from, int $days, \DateTimeImmutable $last, array $closed): ?\DateTimeImmutable
    {
        for ($counted = 0; null !== $from && $counted < $days; ++$counted) {
            $from = $this->nextOpenDay($from, $last, $closed);
        }

        return $from;
    }

    /**
     * The first open day after the one given, or null when the window holds none.
     *
     * @param array<string, true> $closed
     */
    private function nextOpenDay(?\DateTimeImmutable $from, \DateTimeImmutable $last, array $closed): ?\DateTimeImmutable
    {
        if (null === $from) {
            return null;
        }

        do {
            $from = $from->modify('+1 day')->setTime(0, 0);

            if ($from > $last) {
                return null;
            }
        } while (isset($closed[$from->format('Y-m-d')]));

        return $from;
    }
}
