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

use DeliveryDelay\Repository\OrderEstimateRepository;
use DeliveryDelay\Service\EstimateSentence;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Parser\ParserResolver;
use Thelia\Model\Lang;
use Thelia\Model\OrderQuery;

/**
 * Prints the interval of the order after the addresses of the confirmation e-mail.
 *
 * The hook is not email-*.order-confirmation.delivery-address, which the template raises once
 * per delivery module and which replaces the address when it answers: the estimate is an
 * addition to the address. Nothing is printed for an order that has no frozen estimate.
 *
 * The sentence is built in PHP from the language of the order: the hook has to work when the
 * e-mail is sent from a console, with no request to take a language or a template path from.
 */
class OrderConfirmationHook extends BaseHook
{
    public function __construct(
        private readonly OrderEstimateRepository $orderEstimates,
        private readonly EstimateSentence $sentence,
        ?EventDispatcherInterface $dispatcher = null,
        ?ParserResolver $parserResolver = null,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

    public static function getSubscribedHooks(): array
    {
        return [
            'email-html.order-confirmation.after-address' => [
                ['type' => 'email', 'method' => 'onHtml'],
            ],
            'email-txt.order-confirmation.after-address' => [
                ['type' => 'email', 'method' => 'onText'],
            ],
        ];
    }

    public function onHtml(HookRenderEvent $event): void
    {
        $sentence = $this->sentenceFor($event);

        if (null !== $sentence) {
            $event->add('<p style="margin: 10px 0;"><strong>'.htmlspecialchars($sentence, \ENT_QUOTES).'</strong></p>');
        }
    }

    public function onText(HookRenderEvent $event): void
    {
        $sentence = $this->sentenceFor($event);

        if (null !== $sentence) {
            $event->add($sentence."\n");
        }
    }

    private function sentenceFor(HookRenderEvent $event): ?string
    {
        $orderId = (int) $event->getArgument('order');
        $interval = $orderId > 0 ? $this->orderEstimates->find($orderId) : null;

        if (null === $interval) {
            return null;
        }

        $locale = OrderQuery::create()->findPk($orderId)?->getLang()?->getLocale() ?? Lang::getDefaultLanguage()->getLocale();

        return $this->sentence->of($interval, $locale);
    }
}
