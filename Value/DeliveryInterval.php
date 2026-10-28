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
 * The days a parcel is expected between, both included.
 */
final readonly class DeliveryInterval
{
    public function __construct(
        public \DateTimeImmutable $earliest,
        public \DateTimeImmutable $latest,
    ) {
    }

    public function earliestDay(): string
    {
        return $this->earliest->format('Y-m-d');
    }

    public function latestDay(): string
    {
        return $this->latest->format('Y-m-d');
    }

    public function isSingleDay(): bool
    {
        return $this->earliestDay() === $this->latestDay();
    }
}
