<?php


namespace DeliveryDelay\Loop;

use DeliveryDelay\Handler\ProductDelayHandler;
use Thelia\Core\Template\Element\ArraySearchLoopInterface;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class DeliveryDelayProduct extends BaseLoop implements ArraySearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument("product_id")
        );
    }

        
    public function buildArray()
    {
        $productId = $this->getProductId();

        $handler = new ProductDelayHandler();
        $delivery[] = $handler->getDelayForProduct($productId);
        return $delivery;
    }
    
    
    /**
     * @param LoopResult $loopResult
     *
     * @return LoopResult
     */
    public function parseResults(LoopResult $loopResult)
    {
        foreach ($loopResult->getResultDataCollection() as $delivery) {
            $loopResultRow = new LoopResultRow();

            $loopResultRow
                ->set("DELIVERY_TYPE", $delivery["deliveryType"])
                ->set("DELIVERY_START_DATE", $delivery["deliveryStartDate"])
                ->set("DATE_MIN", $delivery["deliveryMin"])
                ->set("DATE_MAX", $delivery["deliveryMax"]);

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
