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

use DeliveryDelay\DeliveryDelay;
use DeliveryDelay\Value\CartLine;
use DeliveryDelay\Value\ProductPageEstimate;
use Thelia\Model\ConfigQuery;
use Thelia\Model\CountryQuery;
use Thelia\Model\Module;
use Thelia\Model\ModuleQuery;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\Module\BaseModule;

/**
 * The estimate a product page shows: one carrier, to the country of the shop, for this product
 * alone. It builds no delivery options and reads neither the session nor the cart, so the page
 * stays the same for every visitor and can be cached.
 */
class ProductPageEstimates
{
    public function __construct(private readonly CarrierEstimates $estimates)
    {
    }

    public function of(int $productId, string $locale, ?\DateTimeImmutable $now = null): ?ProductPageEstimate
    {
        $moduleId = $this->moduleId();
        $countryId = ConfigQuery::getStoreCountry();

        if (null === $moduleId || null === $countryId) {
            return null;
        }

        $product = ProductQuery::create()->findPk($productId);

        if (null === $product || 1 !== (int) $product->getVisible()) {
            return null;
        }

        $variant = ProductSaleElementsQuery::create()->filterByProductId($productId)->filterByIsDefault(1)->findOne();
        $outOfStock = 1 !== (int) $product->getVirtual() && null !== $variant && (float) $variant->getQuantity() <= 0;

        $interval = $this->estimates->forProductLine($moduleId, new CartLine($productId, $outOfStock), $now);
        $country = CountryQuery::create()->findPk($countryId);

        if (null === $interval || null === $country) {
            return null;
        }

        return new ProductPageEstimate($interval, (string) $country->setLocale($locale)->getTitle());
    }

    /**
     * The carrier chosen in the configuration, or the first active delivery module (by position)
     * that has delays.
     */
    public function moduleId(): ?int
    {
        $chosen = ConfigQuery::read(DeliveryDelay::CONFIG_PRODUCT_PAGE_MODULE);

        if (null !== $chosen && '' !== $chosen && ctype_digit((string) $chosen)) {
            $chosenId = (int) $chosen;

            if ($this->isActiveDeliveryModule($chosenId)) {
                return $this->estimates->hasDelays($chosenId) ? $chosenId : null;
            }
        }

        foreach ($this->activeDeliveryModules() as $module) {
            if ($this->estimates->hasDelays((int) $module->getId())) {
                return (int) $module->getId();
            }
        }

        return null;
    }

    /**
     * @return iterable<Module>
     */
    public function activeDeliveryModules(): iterable
    {
        return ModuleQuery::create()
            ->filterByType(BaseModule::DELIVERY_MODULE_TYPE)
            ->filterByActivate(BaseModule::IS_ACTIVATED)
            ->orderByPosition()
            ->orderById()
            ->find();
    }

    private function isActiveDeliveryModule(int $moduleId): bool
    {
        return ModuleQuery::create()
            ->filterById($moduleId)
            ->filterByType(BaseModule::DELIVERY_MODULE_TYPE)
            ->filterByActivate(BaseModule::IS_ACTIVATED)
            ->exists();
    }
}
