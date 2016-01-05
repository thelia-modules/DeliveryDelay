<?php


namespace DeliveryDelay\Form;

use DeliveryDelay\DeliveryDelay;
use DeliveryDelay\Model\ProductDelayQuery;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Symfony\Component\Validator\Constraints;

class DeliveryDelayForm extends BaseForm
{

    protected function buildForm()
    {
        $request = $this->getRequest();
        $productId = $request->get("product_id");

        $productData = ProductDelayQuery::create()
            ->filterByProductId($productId)
        ->findOneOrCreate();

        if (null !== $productId) {
            $data["delivery_min"] = $productData->getDeliveryDelayMin();
            $data["delivery_max"] = $productData->getDeliveryDelayMax();
            $data["restock_min"] = $productData->getRestockDelayMin();
            $data["restock_max"] = $productData->getRestockDelayMax();
            $data["delivery_date_start"] = $productData->getDeliveryDateStart();
            $data["delivery_type"] = $productData->getDeliveryType();
        } else {
            $data["delivery_min"] = DeliveryDelay::getConfigValue("delivery_min");
            $data["delivery_max"] = DeliveryDelay::getConfigValue("delivery_max");
            $data["restock_min"] = DeliveryDelay::getConfigValue("restock_min");
            $data["restock_max"] = DeliveryDelay::getConfigValue("restock_max");
            $data["delivery_date_start"] = null;
            $data["delivery_type"] = null;
        }

        $this->formBuilder
            ->add("delivery_min", "text", array(
                'data'=>$data["delivery_min"],
                'label'=>Translator::getInstance()->trans("Minimum delivery delay (days)", array(), DeliveryDelay::DOMAIN_NAME),
                'label_attr'=>array("for"=>"delivery_min")
            ))
            ->add("delivery_max", "text", array(
                'data'=>$data["delivery_max"],
                'label'=>Translator::getInstance()->trans("Maximum delivery delay (days)", array(), DeliveryDelay::DOMAIN_NAME),
                'label_attr'=>array("for"=>"delivery_max")
            ))
            ->add("restock_min", "text", array(
                'data'=>$data["restock_min"],
                'label'=>Translator::getInstance()->trans("Minimum restock delay (days)", array(), DeliveryDelay::DOMAIN_NAME),
                'label_attr'=>array("for"=>"restock_min")
            ))
            ->add("restock_max", "text", array(
                'data'=>$data["restock_max"],
                'label'=>Translator::getInstance()->trans("Maximum restock delay (days)", array(), DeliveryDelay::DOMAIN_NAME),
                'label_attr'=>array("for"=>"restock_max")
            ))
            ->add("exclude_weekend", "text", array(
                'label'=>Translator::getInstance()->trans("Exclude weekend from delay count", array(), DeliveryDelay::DOMAIN_NAME),
                'data'=>DeliveryDelay::getConfigValue("exclude_weekend"),
                'label_attr'=>array("for"=>"exclude_weekend")
            ))
            ->add("exclude_easter_day", "text", array(
                'label'=>Translator::getInstance()->trans("Exclude easter day from delay count", array(), DeliveryDelay::DOMAIN_NAME),
                'data'=>DeliveryDelay::getConfigValue("exclude_easter_day"),
                'label_attr'=>array("for"=>"exclude_easter_day")
            ))
            ->add("exclude_easter_day_based_holidays", "text", array(
                'label'=>Translator::getInstance()->trans("Exclude holidays based on easter day date", array(), DeliveryDelay::DOMAIN_NAME),
                'data'=>DeliveryDelay::getConfigValue("exclude_easter_day_based_holidays"),
                'label_attr'=>array("for"=>"exclude_easter_day_based_holidays")
            ))
            ->add("delivery_date_start", "text", array(
                'label'=>Translator::getInstance()->trans("This product is only available from", array(), DeliveryDelay::DOMAIN_NAME),
                'data'=>$data['delivery_date_start'],
                'label_attr'=>array("for"=>"delivery_date_start")
            ))
            ->add("delivery_type", "text", array(
                'label'=>Translator::getInstance()->trans("Type of delivery", array(), DeliveryDelay::DOMAIN_NAME),
                'data'=>$data["delivery_type"],
                'label_attr'=>array("for"=>"delivery_type")
            ))
        ;
    }
    
    public function getName()
    {
        return "deliverydelayform";
    }
}
