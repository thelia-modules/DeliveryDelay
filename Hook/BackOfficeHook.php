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

namespace DeliveryDelay\Hook;

use DeliveryDelay\Controller\ProductDelayController;
use DeliveryDelay\Form\ConfigurationType;
use DeliveryDelay\Form\Data\ProductDelaysInput;
use DeliveryDelay\Form\ProductDelaysType;
use DeliveryDelay\Repository\ProductDelayRepository;
use DeliveryDelay\Service\BackOffice\ConfigurationScreen;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;
use Thelia\Core\Event\Hook\HookRenderBlockEvent;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Security\SecurityContext;
use Thelia\Core\Template\Parser\ParserResolver;
use Thelia\Tools\URL;

/**
 * The two places the back office shows the module: its configuration page and a tab on the
 * product sheet. Reading needs the right to see the module, writing the right to update it.
 */
class BackOfficeHook extends BaseHook
{
    private const DOMAIN = 'deliverydelay.bo.default';

    public function __construct(
        private readonly ConfigurationScreen $screen,
        private readonly ProductDelayRepository $productDelays,
        private readonly FormFactoryInterface $formFactory,
        private readonly SecurityContext $securityContext,
        private readonly RequestStack $requestStack,
        #[Autowire(service: 'translator')]
        private readonly TranslatorInterface $messages,
        ?EventDispatcherInterface $dispatcher = null,
        ?ParserResolver $parserResolver = null,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

    /**
     * @return array<string, list<array{type: string, method: string}>>
     */
    public static function getSubscribedHooks(): array
    {
        return [
            'module.configuration' => [
                ['type' => 'back', 'method' => 'onModuleConfiguration'],
            ],
            'product.tab' => [
                ['type' => 'back', 'method' => 'onProductTab'],
            ],
        ];
    }

    public function onModuleConfiguration(HookRenderEvent $event): void
    {
        if (!$this->granted(AccessManager::VIEW)) {
            return;
        }

        $titles = $this->screen->modules($this->locale());
        $form = $this->formFactory->create(ConfigurationType::class, $this->screen->initialData($titles), [
            'module_choices' => array_flip($titles),
            'placeholder_label' => $this->messages->trans('The first carrier that has delays', [], self::DOMAIN, $this->locale()),
        ]);

        $event->add($this->render('DeliveryDelay/configuration.html.twig', [
            'form' => $form->createView(),
            'titles' => $titles,
            'can_update' => $this->granted(AccessManager::UPDATE),
            'closures_url' => URL::getInstance()->absoluteUrl('/admin/configuration/delivery-dates'),
        ]));
    }

    public function onProductTab(HookRenderBlockEvent $event): void
    {
        $productId = (int) $event->getArgument('id');

        if ($productId <= 0 || !$this->granted(AccessManager::VIEW)) {
            return;
        }

        $row = $this->productDelays->find($productId);
        $input = new ProductDelaysInput();
        $input->availabilityDays = $row?->availabilityDays;
        $input->restockDays = $row?->restockDays;

        $event->add([
            'id' => ProductDelayController::TAB,
            'title' => $this->messages->trans('Delivery delay', [], self::DOMAIN, $this->locale()),
            'content' => $this->render('DeliveryDelay/product-tab.html.twig', [
                'form' => $this->formFactory->create(ProductDelaysType::class, $input)->createView(),
                'product_id' => $productId,
                'can_update' => $this->granted(AccessManager::UPDATE),
            ]),
        ]);
    }

    private function granted(string $access): bool
    {
        return $this->securityContext->isGranted(['ADMIN'], [AdminResources::MODULE], ['DeliveryDelay'], [$access]);
    }

    private function locale(): string
    {
        return $this->requestStack->getCurrentRequest()?->getLocale() ?? 'en_US';
    }
}
