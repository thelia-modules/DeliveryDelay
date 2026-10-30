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

namespace DeliveryDelay\Service\BackOffice;

use DeliveryDelay\DeliveryDelay;
use DeliveryDelay\Form\Data\CarrierDelaysInput;
use DeliveryDelay\Form\Data\ConfigurationInput;
use DeliveryDelay\Repository\CarrierDelayRepository;
use DeliveryDelay\Service\CarrierEstimates;
use DeliveryDelay\Service\ProductPageEstimates;
use Thelia\Model\ConfigQuery;
use Thelia\Model\Module;

/**
 * What the configuration screen shows and what it writes.
 */
class ConfigurationScreen
{
    public function __construct(
        private readonly CarrierDelayRepository $carrierDelays,
        private readonly ProductPageEstimates $productPage,
        private readonly CarrierEstimates $estimates,
    ) {
    }

    /**
     * The active delivery modules, by position.
     *
     * @return array<int, string> the title of each, by module id
     */
    public function modules(string $locale): array
    {
        $titles = [];

        /** @var Module $module */
        foreach ($this->productPage->activeDeliveryModules() as $module) {
            $title = (string) $module->setLocale($locale)->getTitle();
            $titles[(int) $module->getId()] = '' !== $title ? $title : (string) $module->getCode();
        }

        return $titles;
    }

    /**
     * @param array<int, string> $titles the modules of the screen
     */
    public function initialData(array $titles): ConfigurationInput
    {
        $delays = $this->carrierDelays->all();
        $input = new ConfigurationInput();

        foreach (array_keys($titles) as $moduleId) {
            $input->carriers[] = CarrierDelaysInput::fromDelays($moduleId, $delays[$moduleId] ?? null);
        }

        $chosen = ConfigQuery::read(DeliveryDelay::CONFIG_PRODUCT_PAGE_MODULE);
        $input->productPageModuleId = null !== $chosen && ctype_digit((string) $chosen) ? (int) $chosen : null;

        return $input;
    }

    /**
     * Writes the rows of the modules the screen offers; a row naming any other module, which only
     * a forged request can carry, is ignored.
     *
     * @param list<int> $allowedModuleIds
     */
    public function save(ConfigurationInput $input, array $allowedModuleIds): void
    {
        $seen = [];

        foreach ($input->carriers as $row) {
            $moduleId = (int) $row->moduleId;

            if (!\in_array($moduleId, $allowedModuleIds, true) || isset($seen[$moduleId])) {
                continue;
            }

            $seen[$moduleId] = true;
            $this->carrierDelays->save($moduleId, $row->toDelays());
        }

        ConfigQuery::write(
            DeliveryDelay::CONFIG_PRODUCT_PAGE_MODULE,
            null !== $input->productPageModuleId && \in_array($input->productPageModuleId, $allowedModuleIds, true)
                ? (string) $input->productPageModuleId
                : '',
        );

        $this->estimates->reset();
    }
}
