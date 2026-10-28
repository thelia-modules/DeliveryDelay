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

use DeliveryDelay\Value\CarrierDelays;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * One row of the configuration screen: the delays of a delivery module. A row left entirely
 * empty means "no delays", and removes them.
 */
final class CarrierDelaysInput
{
    public const TIME_PATTERN = '/^([01]?\d|2[0-3]):[0-5]\d$/';

    public ?int $moduleId = null;

    #[Assert\PositiveOrZero(message: 'The delays are whole numbers of days, zero or more.')]
    public ?int $preparationDays = null;

    #[Assert\PositiveOrZero(message: 'The delays are whole numbers of days, zero or more.')]
    public ?int $transitMinDays = null;

    #[Assert\PositiveOrZero(message: 'The delays are whole numbers of days, zero or more.')]
    public ?int $transitMaxDays = null;

    public ?string $cutoffTime = null;

    public function isEmpty(): bool
    {
        return null === $this->preparationDays
            && null === $this->transitMinDays
            && null === $this->transitMaxDays
            && '' === trim((string) $this->cutoffTime);
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->isEmpty()) {
            return;
        }

        if (null === $this->transitMinDays || null === $this->transitMaxDays) {
            $context->buildViolation('Both transit times are required when a carrier has delays.')->atPath('transitMinDays')->addViolation();
        } elseif ($this->transitMinDays > $this->transitMaxDays) {
            $context->buildViolation('The minimum transit time must not exceed the maximum.')->atPath('transitMinDays')->addViolation();
        }

        $cutoff = trim((string) $this->cutoffTime);

        if ('' !== $cutoff && 1 !== preg_match(self::TIME_PATTERN, $cutoff)) {
            $context->buildViolation('The cut-off time is written HH:MM.')->atPath('cutoffTime')->addViolation();
        }
    }

    public static function fromDelays(int $moduleId, ?CarrierDelays $delays): self
    {
        $input = new self();
        $input->moduleId = $moduleId;

        if (null !== $delays) {
            $input->preparationDays = $delays->preparationDays;
            $input->transitMinDays = $delays->transitMinDays;
            $input->transitMaxDays = $delays->transitMaxDays;
            $input->cutoffTime = null === $delays->cutoffSecond ? null : gmdate('H:i', $delays->cutoffSecond);
        }

        return $input;
    }

    /**
     * The delays to write, null when the row is empty. Call it on a validated row.
     */
    public function toDelays(): ?CarrierDelays
    {
        if ($this->isEmpty()) {
            return null;
        }

        return new CarrierDelays(
            (int) $this->moduleId,
            $this->preparationDays ?? 0,
            (int) $this->transitMinDays,
            (int) $this->transitMaxDays,
            CarrierDelays::secondOfDay($this->cutoffTime),
        );
    }
}
