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

namespace DeliveryDelay\Service;

use DeliveryDelay\DeliveryDelay;
use DeliveryDelay\Value\DeliveryInterval;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The sentence of an estimate, for places that have no template of their own to word it in
 * (the order confirmation e-mail). Worded in the language it is asked for, not in the one of
 * the request: an e-mail is written in the language of the order, in a console as well.
 */
class EstimateSentence
{
    private const DOMAIN = DeliveryDelay::DOMAIN_NAME.'.email.default';

    /**
     * The framework translator, the one the Twig |trans filter reads: the autowired interface is
     * Thelia's own, which does not know the catalogues the module hands to the framework.
     */
    public function __construct(#[Autowire(service: 'translator')] private readonly TranslatorInterface $translator)
    {
    }

    public function of(DeliveryInterval $interval, string $locale): string
    {
        if ($interval->isSingleDay()) {
            return $this->translator->trans(
                'Estimated delivery: %date%',
                ['%date%' => $this->day($interval->earliest, $locale)],
                self::DOMAIN,
                $locale,
            );
        }

        return $this->translator->trans(
            'Estimated delivery: between %earliest% and %latest%',
            ['%earliest%' => $this->day($interval->earliest, $locale), '%latest%' => $this->day($interval->latest, $locale)],
            self::DOMAIN,
            $locale,
        );
    }

    /**
     * "Thursday, November 5, 2026" in the language of the customer.
     */
    public function day(\DateTimeImmutable $day, string $locale): string
    {
        $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::FULL, \IntlDateFormatter::NONE, $day->getTimezone());

        return (string) $formatter->format($day);
    }
}
