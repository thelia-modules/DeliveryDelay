<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DeliveryDelay\Controller;

use DeliveryDelay\Form\Data\ProductDelaysInput;
use DeliveryDelay\Form\ProductDelaysType;
use DeliveryDelay\Repository\ProductDelayRepository;
use DeliveryDelay\Service\BackOffice\Flash;
use DeliveryDelay\Service\CarrierEstimates;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Model\ProductQuery;
use Thelia\Tools\URL;

#[Route('/admin/modules/DeliveryDelay/product', name: 'deliverydelay.product')]
class ProductDelayController extends BaseAdminController
{
    public const TAB = 'delivery-delay';

    #[Route('/{productId}/save', name: '.save', requirements: ['productId' => '\d+'], methods: ['POST'])]
    public function save(
        int $productId,
        Request $request,
        FormFactoryInterface $formFactory,
        ProductDelayRepository $delays,
        CarrierEstimates $estimates,
        Flash $flash,
        #[Autowire(service: 'translator')]
        TranslatorInterface $messages,
    ): Response {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['DeliveryDelay'], AccessManager::UPDATE)) {
            return $response;
        }

        if (!ProductQuery::create()->filterById($productId)->exists()) {
            return $this->errorPage($messages->trans('This product does not exist.', [], 'deliverydelay.bo.default'), 404);
        }

        $form = $formFactory->create(ProductDelaysType::class, new ProductDelaysInput());
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            foreach ($form->getErrors(true) as $error) {
                $flash->add($request, Flash::ERROR, $messages->trans($error->getMessage(), [], 'deliverydelay.bo.default'));
            }
        } else {
            /** @var ProductDelaysInput $input */
            $input = $form->getData();
            $delays->save($productId, $input->availabilityDays, $input->restockDays);
            $estimates->reset();
            $flash->add($request, Flash::SUCCESS, $messages->trans('The delays are saved.', [], 'deliverydelay.bo.default'));
        }

        return new RedirectResponse(URL::getInstance()->absoluteUrl('/admin/products/update', [
            'product_id' => $productId,
            'current_tab' => self::TAB,
        ]));
    }
}
