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

use DeliveryDelay\DeliveryDelay;
use DeliveryDelay\Hook\Theme\ProductPageThemeHook;
use Thelia\Model\ConfigQuery;

final class ProductPageThemeHookTest extends DeliveryDelayTestCase
{
    private ?string $storeCountryBefore = null;

    private ?string $chosenModuleBefore = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storeCountryBefore = ConfigQuery::read('store_country');
        $this->chosenModuleBefore = ConfigQuery::read(DeliveryDelay::CONFIG_PRODUCT_PAGE_MODULE);

        $country = $this->factory->country();
        $country->setLocale('en_US')->setTitle('France')->save();
        $country->setLocale('fr_FR')->setTitle('France')->save();
        ConfigQuery::write('store_country', (string) $country->getId());

        static::getContainer()->get('request_stack')->getMainRequest()->setLocale('en_US');
        static::getContainer()->get('translator')->setLocale('en_US');
    }

    protected function tearDown(): void
    {
        $this->restore('store_country', $this->storeCountryBefore);
        $this->restore(DeliveryDelay::CONFIG_PRODUCT_PAGE_MODULE, $this->chosenModuleBefore);

        parent::tearDown();
    }

    public function testTheProductPageStatesTheDestinationAndTheInterval(): void
    {
        $this->setDelays($this->carrier, 1, 2, 4);
        $product = $this->product(10);

        $html = $this->render($product->getId());

        self::assertStringContainsString('Estimated delivery (France): between Thursday, November 5, 2026 and Saturday, November 7, 2026', $html);
    }

    public function testASingleDayIsStatedOnce(): void
    {
        $this->setDelays($this->carrier, 1, 2, 2);

        $html = $this->render($this->product(10)->getId());

        self::assertStringContainsString('Estimated delivery (France): Thursday, November 5, 2026', $html);
        self::assertStringNotContainsString('between', $html);
    }

    public function testNothingIsShownWithoutDelays(): void
    {
        self::assertSame('', $this->render($this->product(10)->getId()));
    }

    public function testTheProductDelaysAndTheRestockDelayOfTheDefaultVariantCount(): void
    {
        $this->setDelays($this->carrier, 1, 1, 1);
        $inStock = $this->product(5);
        $outOfStock = $this->product(0);
        $this->setProductDelays($inStock, 2, 6);
        $this->setProductDelays($outOfStock, 2, 6);

        // 1 + 2 days: left on Thursday, there on Friday. With the restock: 1 + 8 days, left on Wednesday the 11th, there on the 12th.
        self::assertStringContainsString('Friday, November 6, 2026', $this->render($inStock->getId()));
        self::assertStringContainsString('Thursday, November 12, 2026', $this->render($outOfStock->getId()));
    }

    public function testTheCarrierIsTheFirstActiveOneThatHasDelaysUnlessTheMerchantChoseAnother(): void
    {
        $other = $this->anotherCarrier();
        $this->setDelays($other, 1, 3, 3);
        $product = $this->product(10);

        self::assertStringContainsString('Friday, November 6, 2026', $this->render($product->getId()), 'The carrier without delays is skipped.');

        $this->setDelays($this->carrier, 1, 1, 1);
        ConfigQuery::write(DeliveryDelay::CONFIG_PRODUCT_PAGE_MODULE, (string) $this->carrier->getId());
        self::assertStringContainsString('Wednesday, November 4, 2026', $this->render($product->getId()), 'The chosen carrier wins.');

        $this->setDelays($this->carrier, 1, 1, 1);
        $this->getService(\DeliveryDelay\Repository\CarrierDelayRepository::class)->save((int) $this->carrier->getId(), null);
        $this->getService(\DeliveryDelay\Service\CarrierEstimates::class)->reset();
        self::assertSame('', $this->render($product->getId()), 'A chosen carrier without delays shows nothing.');
    }

    public function testAnotherHookPointOrAProductWithoutIdRendersNothing(): void
    {
        $this->setDelays($this->carrier, 1, 2, 2);
        $hook = $this->getService(ProductPageThemeHook::class);

        self::assertFalse($hook->supports('product.top'));
        self::assertSame('', $hook->render('product.details.bottom', ['product' => []]));
    }

    private function render(int $productId): string
    {
        return trim($this->getService(ProductPageThemeHook::class)->render('product.details.bottom', ['product' => ['id' => $productId]]));
    }

    private function restore(string $name, ?string $value): void
    {
        if (null === $value) {
            ConfigQuery::create()->filterByName($name)->delete();
        } else {
            ConfigQuery::write($name, $value);
        }
        ConfigQuery::resetCache();
    }
}
