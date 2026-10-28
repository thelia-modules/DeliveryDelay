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

use DeliveryDelay\Model\CarrierDelay;
use DeliveryDelay\Model\CarrierDelayQuery;
use DeliveryDelay\Value\CarrierDelays;

class CarrierDelayRepository
{
    /**
     * Every carrier that has delays, one read.
     *
     * @return array<int, CarrierDelays> by module id
     */
    public function all(): array
    {
        $delays = [];

        foreach (CarrierDelayQuery::create()->find() as $row) {
            $delays[(int) $row->getModuleId()] = $this->toValue($row);
        }

        return $delays;
    }

    public function find(int $moduleId): ?CarrierDelays
    {
        $row = CarrierDelayQuery::create()->findOneByModuleId($moduleId);

        return null === $row ? null : $this->toValue($row);
    }

    /**
     * Writes the delays of a carrier, or removes them when there are none.
     */
    public function save(int $moduleId, ?CarrierDelays $delays): void
    {
        $row = CarrierDelayQuery::create()->findOneByModuleId($moduleId);

        if (null === $delays) {
            $row?->delete();

            return;
        }

        ($row ?? (new CarrierDelay())->setModuleId($moduleId))
            ->setPreparationDays($delays->preparationDays)
            ->setTransitMinDays($delays->transitMinDays)
            ->setTransitMaxDays($delays->transitMaxDays)
            ->setCutoffTime(null === $delays->cutoffSecond ? null : gmdate('H:i:s', $delays->cutoffSecond))
            ->save();
    }

    private function toValue(CarrierDelay $row): CarrierDelays
    {
        return new CarrierDelays(
            (int) $row->getModuleId(),
            (int) $row->getPreparationDays(),
            (int) $row->getTransitMinDays(),
            (int) $row->getTransitMaxDays(),
            CarrierDelays::secondOfDay($row->getCutoffTime('H:i:s')),
        );
    }
}
