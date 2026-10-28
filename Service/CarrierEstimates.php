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

namespace DeliveryDelay\Service;

use DeliveryDelay\Estimate\CoreClosedDays;
use DeliveryDelay\Estimate\DeliveryEstimator;
use DeliveryDelay\Estimate\ProductLeadTime;
use DeliveryDelay\Repository\CarrierDelayRepository;
use DeliveryDelay\Repository\ProductDelayRepository;
use DeliveryDelay\Value\CarrierDelays;
use DeliveryDelay\Value\CartLine;
use DeliveryDelay\Value\DeliveryInterval;
use Psr\Clock\ClockInterface;

/**
 * Estimates for the carriers of the shop, with what a request asks twice kept for the rest of
 * it: the delays of the carriers are read once, and so is the calendar (see CoreClosedDays).
 *
 * The delivery options of a module are built several times in one request (once per shipping
 * rule of the cart), and each time asks the same question.
 */
class CarrierEstimates
{
    /** @var array<int, CarrierDelays>|null */
    private ?array $carrierDelays = null;

    public function __construct(
        private readonly CarrierDelayRepository $carrierDelayRepository,
        private readonly ProductDelayRepository $productDelayRepository,
        private readonly DeliveryEstimator $estimator,
        private readonly CoreClosedDays $closedDays,
        private readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param list<CartLine> $lines
     */
    public function forCart(int $moduleId, array $lines, ?\DateTimeImmutable $now = null): ?DeliveryInterval
    {
        $delays = $this->delaysOf($moduleId);

        if (null === $delays) {
            return null;
        }

        $productIds = array_map(static fn (CartLine $line): int => $line->productId, $lines);
        $productDays = ProductLeadTime::slowest($lines, $this->productDelayRepository->forProducts($productIds));

        return $this->estimator->estimate($delays, $productDays, $now ?? $this->clock->now());
    }

    public function forProductLine(int $moduleId, CartLine $line, ?\DateTimeImmutable $now = null): ?DeliveryInterval
    {
        return $this->forCart($moduleId, [$line], $now);
    }

    public function hasDelays(int $moduleId): bool
    {
        return null !== $this->delaysOf($moduleId);
    }

    /**
     * Forgets what was read: for a long-running process, and for a test that changes the delays.
     */
    public function reset(): void
    {
        $this->carrierDelays = null;
        $this->closedDays->reset();
    }

    private function delaysOf(int $moduleId): ?CarrierDelays
    {
        $this->carrierDelays ??= $this->carrierDelayRepository->all();

        return $this->carrierDelays[$moduleId] ?? null;
    }
}
