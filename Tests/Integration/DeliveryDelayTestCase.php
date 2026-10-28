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

use DeliveryDelay\Repository\CarrierDelayRepository;
use DeliveryDelay\Repository\ProductDelayRepository;
use DeliveryDelay\Service\CarrierEstimates;
use DeliveryDelay\Value\CarrierDelays;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Clock\NativeClock;
use Thelia\Domain\Shipping\DeliveryDate\Service\DeliveryDateSettings;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Domain\Order\OrderFacade;
use Thelia\Model\Cart;
use Thelia\Model\CartItem;
use Thelia\Model\LangQuery;
use Thelia\Model\ModuleQuery;
use Thelia\Model\Order;
use Thelia\Model\OrderStatusQuery;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\Model\ConfigQuery;
use Thelia\Model\Module;
use Thelia\Model\Product;
use Thelia\Module\BaseModule;
use Thelia\Test\ActionIntegrationTestCase;
use Thelia\Tests\Support\Delivery\DeliveryDateTestCarrier;
use Thelia\Tests\Support\Delivery\RegistersDeliveryDateTestCarrier;

/**
 * A shop with no closed day, a clock the test sets and a delivery module to speak for.
 */
abstract class DeliveryDelayTestCase extends ActionIntegrationTestCase
{
    use RegistersDeliveryDateTestCarrier;

    /** A Tuesday. */
    protected const string TUESDAY_NINE = '2026-11-03 09:00:00';

    protected Module $carrier;

    private string $shopClosedWeekdaysBefore = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->carrier = $this->registerDeliveryDateTestCarrier(static::getContainer(), $this->getPropelConnection());
        $this->shopClosedWeekdaysBefore = (string) ConfigQuery::read('delivery_closed_weekdays', '');
        ConfigQuery::write('delivery_closed_weekdays', '');
        $this->at(self::TUESDAY_NINE);
    }

    protected function tearDown(): void
    {
        Clock::set(new NativeClock());
        ConfigQuery::write('delivery_closed_weekdays', $this->shopClosedWeekdaysBefore);
        DeliveryDateTestCarrier::$acceptedModes = [];

        parent::tearDown();
    }

    protected function at(string $moment): void
    {
        Clock::set(new MockClock($moment, date_default_timezone_get()));
    }

    protected function setDelays(Module $module, int $preparation, int $transitMin, int $transitMax, ?string $cutoff = null): void
    {
        $this->getService(CarrierDelayRepository::class)->save(
            (int) $module->getId(),
            new CarrierDelays((int) $module->getId(), $preparation, $transitMin, $transitMax, CarrierDelays::secondOfDay($cutoff)),
        );
        $this->getService(CarrierEstimates::class)->reset();
    }

    protected function setProductDelays(Product $product, ?int $availability, ?int $restock): void
    {
        $this->getService(ProductDelayRepository::class)->save((int) $product->getId(), $availability, $restock);
        $this->getService(CarrierEstimates::class)->reset();
    }

    protected function anotherCarrier(): Module
    {
        $module = (new Module())
            ->setCode('DeliveryDelayTestOther'.uniqid())
            ->setFullNamespace(DeliveryDateTestCarrier::class)
            ->setVersion('1.0.0')
            ->setType(BaseModule::DELIVERY_MODULE_TYPE)
            ->setCategory('delivery')
            ->setActivate(BaseModule::IS_ACTIVATED);
        $module->save($this->getPropelConnection());

        return $module;
    }

    protected function closeShopOn(string $day): void
    {
        $this->getService(DeliveryDateSettings::class)->addClosure(null, $day, $day, 'Test');
        $this->getService(CarrierEstimates::class)->reset();
    }

    protected function product(int $stock): Product
    {
        $product = $this->factory->product(
            $this->factory->category(),
            $this->factory->taxRule(),
            $this->factory->currency(),
            ['baseQuantity' => $stock],
        );

        return $product;
    }

    /**
     * @param list<Product> $products
     */
    protected function cartOf(array $products): Cart
    {
        $cart = $this->factory->cart();

        foreach ($products as $product) {
            $this->factory->cartItem($cart, $product);
        }

        return $cart;
    }

    protected function placeOrder(Product $product, ?string $locale = null): Order
    {
        $cart = new Cart();
        $customer = $this->factory->customer($this->factory->customerTitle());
        $cart->setCustomerId($customer->getId())
            ->setCurrencyId($this->factory->currency()->getId())
            ->setToken(uniqid('delivery-delay-', true))
            ->save();

        $variant = ProductSaleElementsQuery::create()->filterByProductId($product->getId())->findOne();
        (new CartItem())
            ->setCartId($cart->getId())
            ->setProductId($product->getId())
            ->setProductSaleElementsId($variant->getId())
            ->setQuantity(1.0)
            ->setPrice('10.00')
            ->setPromoPrice('10.00')
            ->setPromo(0)
            ->save();

        $paymentModule = ModuleQuery::create()->findOneByCode('Cheque');
        self::assertNotNull($paymentModule);

        $sessionOrder = (new Order())
            ->setDeliveryOrderAddressId($this->factory->orderAddress()->getId())
            ->setInvoiceOrderAddressId($this->factory->orderAddress()->getId())
            ->setStatusId(OrderStatusQuery::getNotPaidStatus()->getId())
            ->setDeliveryModuleId($this->carrier->getId())
            ->setPaymentModuleId($paymentModule->getId())
            ->setPostage('0')
            ->setPostageTax('0');

        $placed = $this->getService(OrderFacade::class)->createOrder(
            $this->dispatcher,
            $sessionOrder,
            $this->factory->currency(),
            (null === $locale ? null : LangQuery::create()->findOneByLocale($locale)) ?? $this->factory->lang(),
            $cart,
            $customer,
            useOrderDefinedAddresses: true,
        );

        // What Thelia\Action\Order::create() does next.
        $this->dispatcher->dispatch(new OrderEvent($placed), TheliaEvents::ORDER_BEFORE_PAYMENT);

        return $placed;
    }
}
