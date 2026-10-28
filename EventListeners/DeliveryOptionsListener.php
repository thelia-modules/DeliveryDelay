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

namespace DeliveryDelay\EventListeners;

use DeliveryDelay\Service\CartLines;
use DeliveryDelay\Service\CarrierEstimates;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Api\Bridge\Propel\Event\DeliveryModuleOptionEvent;
use Thelia\Core\Event\TheliaEvents;

/**
 * Puts the interval of dates on the delivery options of a module.
 *
 * It runs after the modules that build the options: CustomDelivery writes an empty string in the
 * dates at priority 129, and what comes after it is what the buyer reads. A carrier with no
 * delays, or a calendar with no open day, leaves the dates as the carrier set them.
 */
final readonly class DeliveryOptionsListener implements EventSubscriberInterface
{
    public const PRIORITY = 100;

    public function __construct(
        private CarrierEstimates $estimates,
        private CartLines $cartLines,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [TheliaEvents::MODULE_DELIVERY_GET_OPTIONS => ['fillDates', self::PRIORITY]];
    }

    public function fillDates(DeliveryModuleOptionEvent $event): void
    {
        $options = $event->getDeliveryModuleOptions();

        if ([] === $options) {
            return;
        }

        $interval = $this->estimates->forCart((int) $event->getModule()->getId(), $this->cartLines->of($event->getCart()));

        if (null === $interval) {
            return;
        }

        foreach ($options as $option) {
            $option
                ->setMinimumDeliveryDate($interval->earliestDay())
                ->setMaximumDeliveryDate($interval->latestDay());
        }
    }
}
