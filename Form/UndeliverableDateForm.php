<?php


namespace DeliveryDelay\Form;

use DeliveryDelay\DeliveryDelay;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Symfony\Component\Validator\Constraints;

class UndeliverableDateForm extends BaseForm
{

    protected function buildForm()
    {
        $this->formBuilder
            ->add("id", TextType::class, array(
                'label'=>Translator::getInstance()->trans("Id", array(), DeliveryDelay::DOMAIN_NAME),
                'label_attr'=>array("for"=>"id")
            ))
            ->add("date", TextType::class, array(
                'label'=>Translator::getInstance()->trans("Undelievrable date", array(), DeliveryDelay::DOMAIN_NAME),
                'label_attr'=>array("for"=>"date")
            ))
            ->add("active", TextType::class, array(
                'label'=>Translator::getInstance()->trans("Exclude or not", array(), DeliveryDelay::DOMAIN_NAME),
                'label_attr'=>array("for"=>"active")
            ))
        ;
    }
    
    public static function getName()
    {
        return "undeliverabledateform";
    }
}
