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

namespace DeliveryDelay\Form;

use DeliveryDelay\Form\Data\CarrierDelaysInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CarrierDelaysType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('moduleId', HiddenType::class)
            ->add('preparationDays', IntegerType::class, ['required' => false, 'attr' => ['min' => 0]])
            ->add('transitMinDays', IntegerType::class, ['required' => false, 'attr' => ['min' => 0]])
            ->add('transitMaxDays', IntegerType::class, ['required' => false, 'attr' => ['min' => 0]])
            ->add('cutoffTime', TextType::class, ['required' => false, 'attr' => ['placeholder' => 'HH:MM', 'maxlength' => 5]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => CarrierDelaysInput::class]);
    }
}
