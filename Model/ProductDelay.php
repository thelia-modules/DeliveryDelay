<?php

namespace DeliveryDelay\Model;

use DeliveryDelay\DeliveryDelay;
use DeliveryDelay\Model\Base\ProductDelay as BaseProductDelay;

class ProductDelay extends BaseProductDelay
{

    public function getDefaultValue()
    {
        $this->setDeliveryDelayMin(DeliveryDelay::getConfigValue("delivery_min", 1))
            ->setDeliveryDelayMax(DeliveryDelay::getConfigValue("delivery_min", 1))
            ->setRestockDelayMin(DeliveryDelay::getConfigValue("restock_min", 1))
            ->setRestockDelayMax(DeliveryDelay::getConfigValue("restock_max", 1))
            ->setDeliveryDateStart(null)
            ->setDeliveryType(null);
        return $this;
    }
}
