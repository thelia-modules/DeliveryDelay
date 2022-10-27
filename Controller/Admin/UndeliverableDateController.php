<?php


namespace DeliveryDelay\Controller\Admin;

use DeliveryDelay\DeliveryDelay;
use DeliveryDelay\Form\UndeliverableDateForm;
use DeliveryDelay\Model\UndeliverableDate;
use DeliveryDelay\Model\UndeliverableDateQuery;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Translation\Translator;

class UndeliverableDateController extends DeliveryDelayController
{
    public function addUndeliverableDate()
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('DeliveryDelay'), AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm(UndeliverableDateForm::getName());

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
                Translator::getInstance()->trans("Error on new undeliverable date : %message", ["message"=>$e->getMessage()], DeliveryDelay::DOMAIN_NAME),
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
        $form = $this->createForm(UndeliverableDateForm::getName());

        try {
            $data = $this->validateForm($form)->getData();

            $undeliverableDate = UndeliverableDateQuery::create()
                ->findOneById($data["id"]);

            if (null === $undeliverableDate) {
                throw new \Exception(Translator::getInstance()->trans("Undeliverable date id doesn't exist"), array(), DeliveryDelay::DOMAIN_NAME);
            }

            $undeliverableDate
                ->setActive($data["active"] ? 1 : 0)
                ->save();

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                Translator::getInstance()->trans("Error updating undeliverable date status : %message", ["message"=>$e->getMessage()], DeliveryDelay::DOMAIN_NAME),
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
        $form = $this->createForm(UndeliverableDateForm::getName());


        try {
            $data = $this->validateForm($form)->getData();

            $undeliverableDate = UndeliverableDateQuery::create()
                ->findOneById($data["id"]);

            if (null === $undeliverableDate) {
                throw new \Exception(Translator::getInstance()->trans(
                    "Undeliverable date id doesn't exist"),
                    array(),
                    DeliveryDelay::DOMAIN_NAME);
            }

            $undeliverableDate->delete();

            return $this->generateSuccessRedirect($form);
        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                Translator::getInstance()->trans(
                    "Error on undeliverable date deletion : %message",
                    ["message"=>$e->getMessage()],
                    DeliveryDelay::DOMAIN_NAME),
                $e->getMessage(),
                $form
            );

            return self::viewAction();
        }
    }
}
