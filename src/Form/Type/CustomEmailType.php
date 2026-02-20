<?php

namespace UbeeDev\AdminBundle\Form\Type;

use UbeeDev\AdminBundle\Form\DataTransformer\EmailTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType as SymfonyEmailType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new EmailTransformer());
    }

    public function getParent(): string
    {
        return SymfonyEmailType::class;
    }
}