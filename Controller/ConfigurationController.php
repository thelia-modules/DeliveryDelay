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

use DeliveryDelay\Form\ConfigurationType;
use DeliveryDelay\Service\BackOffice\ConfigurationScreen;
use DeliveryDelay\Service\BackOffice\Flash;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Tools\URL;

#[Route('/admin/modules/DeliveryDelay', name: 'deliverydelay.config')]
class ConfigurationController extends BaseAdminController
{
    public const SCREEN_URL = '/admin/module/DeliveryDelay';

    #[Route('/save', name: '.save', methods: ['POST'])]
    public function save(
        Request $request,
        FormFactoryInterface $formFactory,
        ConfigurationScreen $screen,
        Flash $flash,
        #[Autowire(service: 'translator')]
        TranslatorInterface $messages,
    ): Response {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['DeliveryDelay'], AccessManager::UPDATE)) {
            return $response;
        }

        $titles = $screen->modules($request->getLocale());
        $form = $formFactory->create(ConfigurationType::class, $screen->initialData($titles), [
            'module_choices' => array_flip($titles),
        ]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            foreach ($this->errorsOf($form, $titles, $messages) as $error) {
                $flash->add($request, Flash::ERROR, $error);
            }
        } else {
            $screen->save($form->getData(), array_keys($titles));
            $flash->add($request, Flash::SUCCESS, $messages->trans('The delays are saved.', [], 'deliverydelay.bo.default'));
        }

        return new RedirectResponse(URL::getInstance()->absoluteUrl(self::SCREEN_URL));
    }

    /**
     * @param array<int, string> $titles
     *
     * @return list<string>
     */
    private function errorsOf(FormInterface $form, array $titles, TranslatorInterface $messages): array
    {
        $domain = 'deliverydelay.bo.default';
        $errors = [];
        $moduleIds = array_keys($titles);

        foreach ($form->get('carriers') as $index => $row) {
            foreach ($row->getErrors(true) as $error) {
                \assert($error instanceof FormError);
                $errors[] = ($titles[$moduleIds[(int) $index] ?? 0] ?? '#'.$index).' : '.$messages->trans($error->getMessage(), [], $domain);
            }
        }

        foreach ($form->getErrors(true) as $error) {
            \assert($error instanceof FormError);
            $message = $messages->trans($error->getMessage(), [], $domain);

            if (!\in_array($message, $errors, true) && !str_ends_with(implode("\n", $errors), ' : '.$message)) {
                $errors[] = $message;
            }
        }

        return [] === $errors ? [$messages->trans('The delays could not be saved.', [], $domain)] : $errors;
    }
}
