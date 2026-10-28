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

namespace DeliveryDelay;

use DeliveryDelay\DependencyInjection\Compiler\PublicServicesForTestsPass;
use DeliveryDelay\DependencyInjection\Compiler\RegisterModuleTranslationsPass;
use DeliveryDelay\Estimate\ClosedDaysProvider;
use DeliveryDelay\Estimate\CoreClosedDays;
use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Thelia\Core\Install\Database;
use Thelia\Module\BaseModule;

/**
 * Tells customers when their parcel arrives, as an interval of dates per carrier.
 *
 * The calendar the count runs on is the delivery calendar of the core (Configuration >
 * Delivery > Delivery dates): the module keeps no list of days off of its own.
 */
class DeliveryDelay extends BaseModule
{
    public const DOMAIN_NAME = 'deliverydelay';

    /** The delivery module whose delays the product page shows. Unset: the first one that has delays. */
    public const CONFIG_PRODUCT_PAGE_MODULE = 'delivery_delay_product_page_module_id';

    public function postActivation(?ConnectionInterface $con = null): void
    {
        $this->createTables($con);
    }

    public function update($currentVersion, $newVersion, ?ConnectionInterface $con = null): void
    {
        $this->createTables($con);
    }

    /**
     * thelia.sql only holds CREATE TABLE IF NOT EXISTS: replayed, it creates what an older
     * version of the module did not have and touches nothing else. It is not guarded by an
     * "initialized" flag because a shop that ran the 2.x module has that flag already, and
     * none of the tables of this version.
     */
    private function createTables(?ConnectionInterface $con): void
    {
        (new Database($con))->insertSql(null, [__DIR__.'/Config/thelia.sql']);
    }

    /**
     * TYPE_BEFORE_REMOVING is what makes the second pass safe: autowiring has already run, so a
     * definition the container cannot build carries its error and is removed instead of being
     * forced into the compiled container.
     */
    public static function getCompilers(): array
    {
        return [
            [new RegisterModuleTranslationsPass(__DIR__, self::DOMAIN_NAME), PassConfig::TYPE_BEFORE_OPTIMIZATION],
            [new PublicServicesForTestsPass(), PassConfig::TYPE_BEFORE_REMOVING],
        ];
    }

    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([
                __DIR__.'/I18n/*',
                __DIR__.'/Tests/*',
                // Generated model stubs and plain values are not services.
                __DIR__.'/Model/*',
                __DIR__.'/Value/*',
                __DIR__.'/DependencyInjection/*',
            ])
            ->autowire(true)
            ->autoconfigure(true);

        // load() registers services under their class name; autowiring an interface needs an alias.
        $servicesConfigurator->alias(ClosedDaysProvider::class, CoreClosedDays::class);
    }
}
