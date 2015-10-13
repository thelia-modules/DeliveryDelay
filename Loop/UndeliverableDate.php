<?php


namespace DeliveryDelay\Loop;

use DeliveryDelay\Model\UndeliverableDateQuery;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use DeliveryDelay\Model\UndeliverableDate as UndeliverableDateModel;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class UndeliverableDate extends BaseLoop implements PropelSearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection();
    }

        
    public function buildModelCriteria()
    {
        return UndeliverableDateQuery::create();
    }
    
    
    /**
     * @param LoopResult $loopResult
     *
     * @return LoopResult
     */
    public function parseResults(LoopResult $loopResult)
    {
        /** @var UndeliverableDateModel $undeliverableDate */
        foreach ($loopResult->getResultDataCollection() as $undeliverableDate) {
            $row = new LoopResultRow($undeliverableDate);
            $row
                ->set("ID", $undeliverableDate->getId())
                ->set("DATE", $undeliverableDate->getDate())
                ->set("ACTIVE", $undeliverableDate->getActive());
            $loopResult->addRow($row);
        }
        return $loopResult;
    }
}
