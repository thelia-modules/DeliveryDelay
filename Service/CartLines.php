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

use DeliveryDelay\Value\CartLine;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Model\Cart;
use Thelia\Model\CartItemQuery;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductSaleElementsQuery;

/**
 * The lines of a cart as the calculation reads them: three queries whatever the number of lines.
 */
class CartLines
{
    /**
     * @param bool $stockAlreadyTaken the order took its quantities out of the stock when it was
     *                                created: what the buyer saw is the stock plus that quantity
     *
     * @return list<CartLine>
     */
    public function of(?Cart $cart, bool $stockAlreadyTaken = false): array
    {
        if (null === $cart) {
            return [];
        }

        $items = CartItemQuery::create()->filterByCartId($cart->getId())->find();

        if (0 === $items->count()) {
            return [];
        }

        $productIds = [];
        $variantIds = [];

        foreach ($items as $item) {
            $productIds[] = (int) $item->getProductId();
            $variantIds[] = (int) $item->getProductSaleElementsId();
        }

        $virtual = [];
        foreach (ProductQuery::create()->filterById(array_unique($productIds), Criteria::IN)->find() as $product) {
            $virtual[(int) $product->getId()] = 1 === (int) $product->getVirtual();
        }

        $stock = [];
        foreach (ProductSaleElementsQuery::create()->filterById(array_unique($variantIds), Criteria::IN)->find() as $variant) {
            $stock[(int) $variant->getId()] = (float) $variant->getQuantity();
        }

        $lines = [];

        foreach ($items as $item) {
            $productId = (int) $item->getProductId();
            $quantity = ($stock[(int) $item->getProductSaleElementsId()] ?? 0.0)
                + ($stockAlreadyTaken ? (float) $item->getQuantity() : 0.0);

            $lines[] = new CartLine($productId, !($virtual[$productId] ?? false) && $quantity <= 0);
        }

        return $lines;
    }
}
