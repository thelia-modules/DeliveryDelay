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

use DeliveryDelay\EventListeners\DeliveryOptionsListener;
use Thelia\Api\Bridge\Propel\Event\DeliveryModuleOptionEvent;
use Thelia\Api\Resource\DeliveryModuleOption;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Model\Cart;
use Thelia\Model\Module;
use Thelia\Test\Trait\RecordsSqlQueries;

final class DeliveryOptionsListenerTest extends DeliveryDelayTestCase
{
    use RecordsSqlQueries;

    public function testTheTwoDatesAreSetOnTheOptionsOfACarrierThatHasDelays(): void
    {
        $this->setDelays($this->carrier, 1, 2, 4);

        $option = $this->options($this->carrier);

        self::assertSame('2026-11-05', $option->getMinimumDeliveryDate());
        self::assertSame('2026-11-07', $option->getMaximumDeliveryDate());
    }

    public function testACarrierWithoutDelaysKeepsTheDatesItGave(): void
    {
        $option = $this->options($this->carrier, minimum: '', maximum: '');

        self::assertSame('', $option->getMinimumDeliveryDate());
        self::assertSame('', $option->getMaximumDeliveryDate());
    }

    public function testTwoCarriersHaveEachTheirOwnInterval(): void
    {
        $other = $this->anotherCarrier();
        $this->setDelays($this->carrier, 1, 1, 1);
        $this->setDelays($other, 2, 3, 5);

        $fast = $this->options($this->carrier);
        $slow = $this->options($other);

        self::assertSame(['2026-11-04', '2026-11-04'], [$fast->getMinimumDeliveryDate(), $fast->getMaximumDeliveryDate()]);
        self::assertSame(['2026-11-07', '2026-11-09'], [$slow->getMinimumDeliveryDate(), $slow->getMaximumDeliveryDate()]);
    }

    public function testTheListenerRunsAfterTheModulesThatWriteEmptyDates(): void
    {
        // CustomDelivery appends its option, with empty dates, at priority 129.
        self::assertLessThan(129, DeliveryOptionsListener::PRIORITY);

        $this->setDelays($this->carrier, 1, 2, 2);

        $option = $this->options($this->carrier, minimum: '', maximum: '');

        self::assertSame('2026-11-05', $option->getMinimumDeliveryDate(), 'The dates written by the carrier are replaced.');
    }

    public function testAClosedDayOfTheShopMovesTheInterval(): void
    {
        $this->setDelays($this->carrier, 1, 2, 2);
        $this->closeShopOn('2026-11-04');

        $option = $this->options($this->carrier);

        self::assertSame('2026-11-06', $option->getMinimumDeliveryDate());
    }

    public function testTheSlowestProductOfTheCartSetsThePreparation(): void
    {
        $this->setDelays($this->carrier, 1, 2, 2);
        $quick = $this->product(10);
        $slow = $this->product(10);
        $this->setProductDelays($quick, 1, null);
        $this->setProductDelays($slow, 5, null);

        $option = $this->options($this->carrier, $this->cartOf([$quick, $slow]));

        // 1 + 5 days of preparation, no closed day: left on Sunday the 8th, there on Tuesday the 10th.
        self::assertSame('2026-11-10', $option->getMinimumDeliveryDate());
    }

    public function testTheRestockDelayCountsOnlyWhenTheVariantIsOutOfStock(): void
    {
        $this->setDelays($this->carrier, 1, 1, 1);
        $inStock = $this->product(3);
        $outOfStock = $this->product(0);
        $this->setProductDelays($inStock, 0, 4);
        $this->setProductDelays($outOfStock, 0, 4);

        $withStock = $this->options($this->carrier, $this->cartOf([$inStock]));
        $without = $this->options($this->carrier, $this->cartOf([$outOfStock]));

        self::assertSame('2026-11-04', $withStock->getMinimumDeliveryDate());
        // 1 + 4 days: left on Saturday the 7th, there on Sunday the 8th.
        self::assertSame('2026-11-08', $without->getMinimumDeliveryDate());
    }

    public function testAVirtualProductIsNeverOutOfStock(): void
    {
        $this->setDelays($this->carrier, 1, 1, 1);
        $virtual = $this->product(0);
        $virtual->setVirtual(1)->save();
        $this->setProductDelays($virtual, 0, 4);

        $option = $this->options($this->carrier, $this->cartOf([$virtual]));

        self::assertSame('2026-11-04', $option->getMinimumDeliveryDate());
    }

    public function testAnEventRaisedTwiceForTheSameCarrierReadsTheDelaysAndTheCalendarOnce(): void
    {
        $this->setDelays($this->carrier, 1, 2, 2);
        $this->options($this->carrier);
        $queries = $this->recordSqlQueries(fn () => $this->options($this->carrier));

        // The second event reads nothing about the carrier: no delays, no closure, no rule.
        foreach (['delivery_delay_carrier', 'delivery_closure', 'delivery_date_rule'] as $table) {
            self::assertSame(0, self::countSqlQueriesSelectingFrom($queries, $table), $table.' is read once per request.');
        }
    }

    private function options(Module $module, ?Cart $cart = null, string $minimum = '', string $maximum = ''): DeliveryModuleOption
    {
        $country = $this->factory->country();
        $event = new DeliveryModuleOptionEvent($module, null, $cart ?? $this->factory->cart(), $country);

        // What a carrier that builds its options does, at the priority CustomDelivery has.
        $listener = static function (DeliveryModuleOptionEvent $event) use ($module, $minimum, $maximum): void {
            $event->appendDeliveryModuleOptions(
                (new DeliveryModuleOption())
                    ->setCode($module->getCode())
                    ->setValid(true)
                    ->setTitle('Test')
                    ->setImage('')
                    ->setMinimumDeliveryDate($minimum)
                    ->setMaximumDeliveryDate($maximum),
            );
        };

        $this->dispatcher->addListener(TheliaEvents::MODULE_DELIVERY_GET_OPTIONS, $listener, 129);
        try {
            $this->dispatcher->dispatch($event, TheliaEvents::MODULE_DELIVERY_GET_OPTIONS);
        } finally {
            $this->dispatcher->removeListener(TheliaEvents::MODULE_DELIVERY_GET_OPTIONS, $listener);
        }

        $options = $event->getDeliveryModuleOptions();
        self::assertCount(1, $options);

        return $options[0];
    }
}
