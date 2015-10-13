<?php


namespace DeliveryDelay\Handler;

use DeliveryDelay\DeliveryDelay;
use DeliveryDelay\Model\ProductDelay;
use DeliveryDelay\Model\ProductDelayQuery;
use DeliveryDelay\Model\UndeliverableDateQuery;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductSaleElements;

class ProductDelayHandler
{
    /**
     * @param $productId
     * @return array
     */
    public function getDelayForProduct($productId)
    {
        $delay = ProductDelayQuery::create()
            ->filterByProductId($productId)
            ->findOneOrCreate();

        $defaultDelay = (new ProductDelay())->getDefaultValue();

        $product = ProductQuery::create()
            ->findOneById($productId);

        if (!$product) {
            return null;
        }

        $quantity = $this->productHasQuantity($product);

        if (true === $quantity) {
            $delayMin = $delay->getDeliveryDelayMin() ? $delay->getDeliveryDelayMin() : $defaultDelay->getDeliveryDelayMin();
            $delayMax = $delay->getDeliveryDelayMax() ? $delay->getDeliveryDelayMax() : $defaultDelay->getDeliveryDelayMax();
        } else {
            $delayMin = $delay->getRestockDelayMin() ? $delay->getRestockDelayMin() : $defaultDelay->getRestockDelayMin();
            $delayMax = $delay->getRestockDelayMax() ? $delay->getRestockDelayMax() : $defaultDelay->getRestockDelayMax();
        }

        $startDate = date("Y-m-d");
        $delivery["deliveryDateStart"] = null;

        if (null !== $delay->getDeliveryDateStart() && time() < strtotime($delay->getDeliveryDateStart())) {
            $startDate = $delivery["deliveryDateStart"] = $delay->getDeliveryDateStart();
        }

        $delivery["deliveryType"] = $delay->getDeliveryType();

        $delivery["deliveryMin"] = $this->computeDeliveryDate($startDate, $delayMin);
        $delivery["deliveryMax"] = $this->computeDeliveryDate($startDate, $delayMax);

        return $delivery;
    }

    public function computeDeliveryDate($startDate, $delay) {
        $date = $startDate;

        while ($delay > 0) {
            $date = date("Y-m-d", strtotime($date . ' +1 day'));
            //If the date is deliverable decrease the delay
            if ($this->checkIsDeliverableDate($date)) {
                $delay--;
            }
        }

        return $date;
    }

    public function checkIsDeliverableDate($date)
    {
        //If config say exclude weekend and the date is on weekend return false
        if (DeliveryDelay::getConfigValue("exclude_weekend") && date("N", strtotime($date)) > 5) {
            return false;
        }

        $undeliverableDates = UndeliverableDateQuery::create()
            ->filterByActive(true)
            ->select("date")
            ->find()
            ->toArray();

        if (in_array(date("m-d", strtotime($date)), $undeliverableDates)) {
            return false;
        }

        if (DeliveryDelay::getConfigValue("exclude_easter_day") && true === $this->isEasterDay($date)) {
            return false;
        }

        return true;
    }

    public function isEasterDay($date)
    {
        $easterDay = date("Y-m-d", easter_date(date("Y")));
        if ($easterDay === $date) {
            return true;
        }
        return false;
    }

    public function productHasQuantity(Product $product)
    {
        /** @var ProductSaleElements $productSaleElements */
        foreach ($product->getProductSaleElementss() as $productSaleElements) {
            if ($productSaleElements->getQuantity() > 0) {
                return true;
            }
            return false;
        }
    }
}
