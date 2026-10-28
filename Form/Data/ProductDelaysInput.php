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

/**
 * The two delays of a product. Empty is "not filled in"; 0 is a delay of zero days.
 */
final class ProductDelaysInput
{
    #[Assert\PositiveOrZero(message: 'The delays are whole numbers of days, zero or more.')]
    public ?int $availabilityDays = null;

    #[Assert\PositiveOrZero(message: 'The delays are whole numbers of days, zero or more.')]
    public ?int $restockDays = null;
}
