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

use DeliveryDelay\Form\Data\ConfigurationInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('carriers', CollectionType::class, [
                'entry_type' => CarrierDelaysType::class,
                'allow_add' => false,
                'allow_delete' => false,
            ])
            ->add('productPageModuleId', ChoiceType::class, [
                'required' => false,
                'choices' => $options['module_choices'],
                'placeholder' => $options['placeholder_label'],
                'translation_domain' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConfigurationInput::class,
            'module_choices' => [],
            'placeholder_label' => 'Automatic',
        ]);
        $resolver->setAllowedTypes('module_choices', 'array');
        $resolver->setAllowedTypes('placeholder_label', 'string');
    }
}
