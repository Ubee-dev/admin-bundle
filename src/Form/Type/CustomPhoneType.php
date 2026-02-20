<?php

namespace UbeeDev\AdminBundle\Form\Type;

use UbeeDev\AdminBundle\Form\DataTransformer\PhoneNumberTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomPhoneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new PhoneNumberTransformer());
    }

    public function getParent(): string
    {
        return TelType::class;
    }
}