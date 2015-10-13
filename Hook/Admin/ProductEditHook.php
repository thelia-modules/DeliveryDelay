<?php


namespace DeliveryDelay\Hook\Admin;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class ProductEditHook extends BaseHook
{
    public function onProductTabContent(HookRenderEvent $event)
    {
        $event->add($this->render(
            'delivery-delay/product/product-edit.html'
        ));
    }

    public function onProductEditJs(HookRenderEvent $event)
    {
        $event->add($this->render(
            'delivery-delay/product/product-edit-js.html'
        ));
    }
}
