<?php


namespace DeliveryDelay\Controller\Admin;

use DeliveryDelay\DeliveryDelay;
use DeliveryDelay\Model\ProductDelayQuery;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;

class ConfigurationController extends DeliveryDelayController
{
    public function setGlobalConfig()
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('DeliveryDelay'), AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm("deliverydelay.form");

        try {
            $data = $this->validateForm($form)->getData();

            DeliveryDelay::setConfigValue("delivery_min", $data["delivery_min"]);
            DeliveryDelay::setConfigValue("delivery_max", $data["delivery_max"]);
            DeliveryDelay::setConfigValue("restock_min", $data["restock_min"]);
            DeliveryDelay::setConfigValue("restock_max", $data["restock_max"]);

            $excludeWeekend = $data["exclude_weekend"] === "on" ? 1 :0 ;
            $excludeEasterDay = $data["exclude_easter_day"] === "on" ? 1 :0 ;
            DeliveryDelay::setConfigValue("exclude_weekend", $excludeWeekend);
            DeliveryDelay::setConfigValue("exclude_easter_day", $excludeEasterDay);

            return $this->generateSuccessRedirect($form);

        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("Error on delivery delay configuration : %message", ["message"=>$e->getMessage()], DeliveryDelay::DOMAIN_NAME),
                $e->getMessage(),
                $form
            );

            return self::viewAction();
        }
    }

    public function setProductConfig($product_id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('DeliveryDelay'), AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm("deliverydelay.form");

        try {
            $data = $this->validateForm($form)->getData();

            $productDelay = ProductDelayQuery::create()
                ->filterByProductId($product_id)
                ->findOneOrCreate();

            $productDelay->setDeliveryDelayMin($data["delivery_min"])
                ->setDeliveryDelayMax($data["delivery_max"])
                ->setRestockDelayMin($data["restock_min"])
                ->setRestockDelayMax($data["restock_max"])
                ->setDeliveryDateStart($data["delivery_date_start"])
                ->setDeliveryType($data["delivery_type"])
                ->save();

            return new JsonResponse($this->getTranslator()->trans("Delivery delay product configuration updated with success!", [], DeliveryDelay::DOMAIN_NAME));
        } catch (\Exception $e) {

            $message = $this->getTranslator()->trans("Error on delivery delay product configuration : %message", ["%message"=>$e->getMessage()], DeliveryDelay::DOMAIN_NAME);
            $form->setErrorMessage($message);

            $this->getParserContext()
                ->addForm($form)
                ->setGeneralError($message)
            ;

            return new JsonResponse($message, 500);
        }
    }
}
