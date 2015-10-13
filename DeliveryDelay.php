<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace DeliveryDelay;

use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Core\Template\TemplateDefinition;
use Thelia\Install\Database;
use Thelia\Module\BaseModule;

class DeliveryDelay extends BaseModule
{
    /** @var string */
    const DOMAIN_NAME = 'deliverydelay';

    /*
     * You may now override BaseModuleInterface methods, such as:
     * install, destroy, preActivation, postActivation, preDeactivation, postDeactivation
     *
     * Have fun !
     */

    public function postActivation(ConnectionInterface $con = null)
    {
        if (!self::getConfigValue('is_initialized', false)) {
            $database = new Database($con);
            $database->insertSql(null, [__DIR__ . "/Config/thelia.sql"]);
            self::setConfigValue('is_initialized', true);
        }
    }

    public function getHooks()
    {
        return array(
            array(
                "type" => TemplateDefinition::FRONT_OFFICE,
                "code" => "product.delivery-delay",
                "title" => array(
                    "fr_FR" => "Hook Produit DeliveryDelay",
                    "en_US" => "DeliveryDelay Product Hook",
                ),
                "description" => array(
                    "fr_FR" => "Hook pour délais de livraison d'un produit",
                    "en_US" => "Hook for product delivery delay",
                ),
                "active" => true
            )
        );
    }
}
