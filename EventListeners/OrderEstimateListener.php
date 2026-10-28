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

use DeliveryDelay\Repository\OrderEstimateRepository;
use DeliveryDelay\Service\CartLines;
use DeliveryDelay\Service\CarrierEstimates;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\CartQuery;

/**
 * Keeps the interval the customer was told when the order is placed.
 *
 * ORDER_BEFORE_PAYMENT is raised by the order action right after the order and its lines are
 * written, and is also what sends the confirmation e-mail (priority 128): the estimate is
 * written before, so the e-mail can print it. It is written once, never recomputed.
 *
 * Placing an order does not fail because an estimate could not be written.
 */
final readonly class OrderEstimateListener implements EventSubscriberInterface
{
    public const PRIORITY = 160;

    public function __construct(
        private CarrierEstimates $estimates,
        private CartLines $cartLines,
        private OrderEstimateRepository $orderEstimates,
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [TheliaEvents::ORDER_BEFORE_PAYMENT => ['freeze', self::PRIORITY]];
    }

    public function freeze(OrderEvent $event, string $eventName, EventDispatcherInterface $dispatcher): void
    {
        $order = $event->hasPlacedOrder() ? $event->getPlacedOrder() : $event->getOrder();
        $moduleId = $order->getDeliveryModuleId();

        if (null === $moduleId || null === $order->getId()) {
            return;
        }

        try {
            $cart = CartQuery::create()->findPk($order->getCartId());
            $lines = $this->cartLines->of($cart, $order->isStockManagedOnOrderCreation($dispatcher));
            $interval = $this->estimates->forCart($moduleId, $lines);

            if (null !== $interval) {
                $this->orderEstimates->freeze((int) $order->getId(), $moduleId, $interval);
            }
        } catch (\Throwable $failure) {
            $this->logger->error('The delivery estimate of order {order} could not be written.', [
                'order' => $order->getId(),
                'exception' => $failure,
            ]);
        }
    }
}
