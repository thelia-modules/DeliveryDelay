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

namespace DeliveryDelay\Tests\Integration;

use DeliveryDelay\EventListeners\OrderEstimateListener;
use DeliveryDelay\Model\OrderEstimateQuery;
use DeliveryDelay\Repository\OrderEstimateRepository;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\Payment\ManageStockOnCreationEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\Order;
use Thelia\Model\ProductSaleElementsQuery;

final class OrderEstimateListenerTest extends DeliveryDelayTestCase
{
    public function testPlacingAnOrderWritesTheIntervalOnce(): void
    {
        $this->setDelays($this->carrier, 1, 2, 4);

        $order = $this->placeOrder($this->product(10));

        $row = OrderEstimateQuery::create()->findOneByOrderId($order->getId());
        self::assertNotNull($row, 'The estimate is written when the order is placed.');
        self::assertSame(['2026-11-05', '2026-11-07'], [$row->getEarliestDate('Y-m-d'), $row->getLatestDate('Y-m-d')]);
        self::assertSame($this->carrier->getId(), $row->getModuleId());
        self::assertSame(1, OrderEstimateQuery::create()->filterByOrderId($order->getId())->count());
    }

    public function testTheIntervalIsNotRewrittenWhenTheDelaysChangeOrTheEventIsRaisedAgain(): void
    {
        $this->setDelays($this->carrier, 1, 2, 4);
        $order = $this->placeOrder($this->product(10));

        $this->setDelays($this->carrier, 3, 5, 9);
        $this->at('2026-11-10 09:00:00');
        $this->dispatcher->dispatch(new OrderEvent($order), TheliaEvents::ORDER_BEFORE_PAYMENT);

        $frozen = $this->getService(OrderEstimateRepository::class)->find((int) $order->getId());
        self::assertNotNull($frozen);
        self::assertSame(['2026-11-05', '2026-11-07'], [$frozen->earliestDay(), $frozen->latestDay()]);
    }

    public function testAnOrderForACarrierWithoutDelaysHasNoEstimate(): void
    {
        $order = $this->placeOrder($this->product(10));

        self::assertSame(0, OrderEstimateQuery::create()->filterByOrderId($order->getId())->count());
    }

    public function testTheLastUnitTakenByTheOrderItselfDoesNotAddTheRestockDelay(): void
    {
        $this->setDelays($this->carrier, 1, 1, 1);
        $product = $this->product(1);
        $this->setProductDelays($product, 0, 4);

        // The payment module takes the stock when the order is created: the last unit is gone
        // when the estimate is written, but the buyer was told "in stock".
        $takeStock = static function (ManageStockOnCreationEvent $event): void {
            $event->setManageStock(true);
        };
        $eventName = TheliaEvents::getModuleEvent(TheliaEvents::MODULE_PAYMENT_MANAGE_STOCK, 'Cheque');
        $this->dispatcher->addListener($eventName, $takeStock);

        try {
            $order = $this->placeOrder($product);
        } finally {
            $this->dispatcher->removeListener($eventName, $takeStock);
        }

        $variant = ProductSaleElementsQuery::create()->filterByProductId($product->getId())->findOne();
        self::assertSame(0.0, (float) $variant->getQuantity(), 'The order took the last unit.');

        $row = OrderEstimateQuery::create()->findOneByOrderId($order->getId());
        self::assertNotNull($row);
        self::assertSame('2026-11-04', $row->getEarliestDate('Y-m-d'));
    }

    public function testTheEstimateOutlivesTheCarrier(): void
    {
        $this->setDelays($this->carrier, 1, 2, 2);
        $order = $this->placeOrder($this->product(10));
        $moduleId = $this->carrier->getId();

        // What the core does to the orders of a module it removes.
        $order->setDeliveryModuleId(null)->save();
        $this->carrier->delete();

        $row = OrderEstimateQuery::create()->findOneByOrderId($order->getId());
        self::assertNotNull($row, 'No foreign key ties the estimate to the carrier.');
        self::assertSame($moduleId, $row->getModuleId());
    }

    public function testTheEstimateGoesWithTheOrder(): void
    {
        $this->setDelays($this->carrier, 1, 2, 2);
        $order = $this->placeOrder($this->product(10));

        $order->delete();

        self::assertSame(0, OrderEstimateQuery::create()->filterByOrderId($order->getId())->count());
    }

    public function testTheListenerRunsBeforeTheConfirmationEmailIsSent(): void
    {
        // ORDER_BEFORE_PAYMENT sends the confirmation e-mail at priority 128.
        self::assertGreaterThan(128, OrderEstimateListener::PRIORITY);
    }
}
