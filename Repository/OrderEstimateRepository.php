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

namespace DeliveryDelay\Repository;

use DeliveryDelay\Model\OrderEstimate;
use DeliveryDelay\Model\OrderEstimateQuery;
use DeliveryDelay\Value\DeliveryInterval;

class OrderEstimateRepository
{
    public function find(int $orderId): ?DeliveryInterval
    {
        $row = OrderEstimateQuery::create()->findOneByOrderId($orderId);

        if (null === $row) {
            return null;
        }

        $earliest = $this->day($row->getEarliestDate('Y-m-d'));
        $latest = $this->day($row->getLatestDate('Y-m-d'));

        return null === $earliest || null === $latest ? null : new DeliveryInterval($earliest, $latest);
    }

    /**
     * Written once. An order that already has its estimate keeps it, whatever the delays say now.
     *
     * @return bool whether a row was written
     */
    public function freeze(int $orderId, ?int $moduleId, DeliveryInterval $interval): bool
    {
        if (OrderEstimateQuery::create()->filterByOrderId($orderId)->exists()) {
            return false;
        }

        (new OrderEstimate())
            ->setOrderId($orderId)
            ->setModuleId($moduleId)
            ->setEarliestDate($interval->earliestDay())
            ->setLatestDate($interval->latestDay())
            ->save();

        return true;
    }

    private function day(?string $date): ?\DateTimeImmutable
    {
        return null === $date ? null : (\DateTimeImmutable::createFromFormat('!Y-m-d', $date) ?: null);
    }
}
