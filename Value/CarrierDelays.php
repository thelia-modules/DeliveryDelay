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
 * The delays of one delivery module, as the calculation reads them.
 */
final readonly class CarrierDelays
{
    /**
     * @param int|null $cutoffSecond seconds since midnight after which an order waits for the next
     *                               working day, null when the carrier has no cut-off time
     */
    public function __construct(
        public int $moduleId,
        public int $preparationDays,
        public int $transitMinDays,
        public int $transitMaxDays,
        public ?int $cutoffSecond,
    ) {
    }

    /**
     * Seconds since midnight of a "HH:MM" or "HH:MM:SS" time, null for an empty one.
     */
    public static function secondOfDay(?string $time): ?int
    {
        if (null === $time || '' === trim($time)) {
            return null;
        }

        if (1 !== preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', trim($time), $parts)) {
            throw new \InvalidArgumentException(\sprintf('"%s" is not a time of day.', $time));
        }

        $hours = (int) $parts[1];
        $minutes = (int) $parts[2];
        $seconds = (int) ($parts[3] ?? 0);

        if ($hours > 23 || $minutes > 59 || $seconds > 59) {
            throw new \InvalidArgumentException(\sprintf('"%s" is not a time of day.', $time));
        }

        return $hours * 3600 + $minutes * 60 + $seconds;
    }
}
