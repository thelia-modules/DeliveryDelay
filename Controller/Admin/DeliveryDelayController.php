<?php


namespace DeliveryDelay\Controller\Admin;

use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;

class DeliveryDelayController extends BaseAdminController
{
    public function viewAction()
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('DeliveryDelay'), AccessManager::VIEW)) {
            return $response;
        }

        return $this->render("delivery-delay/configuration");
    }

}
