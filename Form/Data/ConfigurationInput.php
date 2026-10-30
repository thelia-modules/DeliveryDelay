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

namespace DeliveryDelay\Form\Data;

use Symfony\Component\Validator\Constraints as Assert;

final class ConfigurationInput
{
    /** @var list<CarrierDelaysInput> */
    #[Assert\Valid]
    public array $carriers = [];

    /** The delivery module the product page speaks for, null for the first one that has delays. */
    public ?int $productPageModuleId = null;
}
