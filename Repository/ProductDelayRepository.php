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

use DeliveryDelay\Model\ProductDelay;
use DeliveryDelay\Model\ProductDelayQuery;
use DeliveryDelay\Value\ProductDelayRow;

class ProductDelayRepository
{
    /**
     * @param list<int> $productIds
     *
     * @return array<int, ProductDelayRow> by product id, one query whatever the number of products
     */
    public function forProducts(array $productIds): array
    {
        if ([] === $productIds) {
            return [];
        }

        $rows = [];

        foreach (ProductDelayQuery::create()->filterByProductId(array_values(array_unique($productIds)), \Propel\Runtime\ActiveQuery\Criteria::IN)->find() as $row) {
            $rows[(int) $row->getProductId()] = $this->toValue($row);
        }

        return $rows;
    }

    public function find(int $productId): ?ProductDelayRow
    {
        return $this->forProducts([$productId])[$productId] ?? null;
    }

    /**
     * Writes the delays of a product. A product with neither delay filled in has no row.
     */
    public function save(int $productId, ?int $availabilityDays, ?int $restockDays): void
    {
        $row = ProductDelayQuery::create()->findOneByProductId($productId);

        if (null === $availabilityDays && null === $restockDays) {
            $row?->delete();

            return;
        }

        ($row ?? (new ProductDelay())->setProductId($productId))
            ->setAvailabilityDays($availabilityDays)
            ->setRestockDays($restockDays)
            ->save();
    }

    private function toValue(ProductDelay $row): ProductDelayRow
    {
        return new ProductDelayRow((int) $row->getProductId(), $row->getAvailabilityDays(), $row->getRestockDays());
    }
}
