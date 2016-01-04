<?php


namespace DeliveryDelay\Controller\Admin;

use DeliveryDelay\DeliveryDelay;
use DeliveryDelay\Model\ProductDelayQuery;
use DeliveryDelay\Model\UndeliverableDate;
use DeliveryDelay\Model\UndeliverableDateQuery;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;

class UndeliverableDateController extends DeliveryDelayController
{
    public function addUndeliverableDate()
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('DeliveryDelay'), AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm("undeliverabledate.form");

        try {
            $data = $this->validateForm($form)->getData();

            $undeliverableDate =  new UndeliverableDate();

            $date = date("m-d", strtotime(date('Y')."-".$data["date"]));
            $undeliverableDate->setDate($date)
                ->setActive($data["active"] === "on" ? 1 : 0)
                ->save();

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("Error on new undeliverable date : %message", ["message"=>$e->getMessage()], DeliveryDelay::DOMAIN_NAME),
                $e->getMessage(),
                $form
            );

            return self::viewAction();
        }
    }

    public function updateUndeliverableDate()
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('DeliveryDelay'), AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm("undeliverabledate.form");

        try {
            $data = $this->validateForm($form)->getData();

            $undeliverableDate = UndeliverableDateQuery::create()
                ->findOneById($data["id"]);

            if (null === $undeliverableDate) {
                throw new \Exception($this->getTranslator()->trans("Undeliverable date id doesn't exist"), array(), DeliveryDelay::DOMAIN_NAME);
            }

            $undeliverableDate
                ->setActive($data["active"] ? 1 : 0)
                ->save();

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("Error updating undeliverable date status : %message", ["message"=>$e->getMessage()], DeliveryDelay::DOMAIN_NAME),
                $e->getMessage(),
                $form
            );

            return self::viewAction();
        }
    }

    public function deleteUndeliverableDate()
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('DeliveryDelay'), AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm("undeliverabledate.form");

        try {
            $data = $this->validateForm($form)->getData();

            $undeliverableDate = UndeliverableDateQuery::create()
                ->findOneById($data["id"]);

            if (null === $undeliverableDate) {
                throw new \Exception($this->getTranslator()->trans("Undeliverable date id doesn't exist"), array(), DeliveryDelay::DOMAIN_NAME);
            }

            $undeliverableDate->delete();

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("Error on undeliverable date deletion : %message", ["message"=>$e->getMessage()], DeliveryDelay::DOMAIN_NAME),
                $e->getMessage(),
                $form
            );

            return self::viewAction();
        }
    }
}
