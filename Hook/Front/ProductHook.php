<?php


namespace DeliveryDelay\Hook\Front;

use DeliveryDelay\Handler\ProductDelayHandler;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class ProductHook extends BaseHook
{
    public function onProductDeliveryDelay(HookRenderEvent $event)
    {
        $productId = $event->getArgument('product');

        $handler = new ProductDelayHandler();
        $delivery = $handler->getDelayForProduct($productId);

        $event->add($this->render(
            'delivery-delay/product-delivery-delay.html',
            $delivery
        ));
    }

    public function onProductDetailsBottom(HookRenderEvent $event)
    {
       $this->onProductDeliveryDelay($event) ;
    }
}
