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

namespace DeliveryDelay\Hook\Theme;

use DeliveryDelay\Service\EstimateSentence;
use DeliveryDelay\Service\ProductPageEstimates;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Hook\Theme\ThemeHookInterface;
use Thelia\Model\Lang;
use Thelia\Model\LangQuery;
use Twig\Environment;

/**
 * The estimated delivery of a product, under its details on the product page of a Twig theme.
 *
 * Flexy raises product.details.bottom with the product it shows. Nothing is rendered when the
 * module has no carrier with delays to speak for.
 */
final readonly class ProductPageThemeHook implements ThemeHookInterface
{
    public const HOOK = 'product.details.bottom';

    private const TEMPLATE = '@DeliveryDelayModule/theme_hook/delivery-estimate.html.twig';

    public function __construct(
        private Environment $twig,
        private ProductPageEstimates $estimates,
        private EstimateSentence $sentence,
        private RequestStack $requestStack,
    ) {
    }

    public function supports(string $hookName): bool
    {
        return self::HOOK === $hookName;
    }

    public function render(string $hookName, array $parameters): string
    {
        $product = $parameters['product'] ?? null;
        $productId = \is_array($product) ? ($product['id'] ?? null) : (\is_object($product) && method_exists($product, 'getId') ? $product->getId() : null);

        if (!is_numeric($productId)) {
            return '';
        }

        $locale = $this->locale();
        $estimate = $this->estimates->of((int) $productId, $locale);

        if (null === $estimate) {
            return '';
        }

        return $this->twig->render(self::TEMPLATE, [
            'country' => $estimate->countryTitle,
            'single_day' => $estimate->interval->isSingleDay(),
            'earliest' => $this->sentence->day($estimate->interval->earliest, $locale),
            'latest' => $this->sentence->day($estimate->interval->latest, $locale),
        ]);
    }

    private function locale(): string
    {
        $requested = $this->requestStack->getMainRequest()?->getLocale();

        if (null !== $requested && null !== LangQuery::create()->findOneByLocale($requested)) {
            return $requested;
        }

        return Lang::getDefaultLanguage()->getLocale();
    }
}
