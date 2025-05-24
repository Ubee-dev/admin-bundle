<?php

namespace Khalil1608\AdminBundle\Form\Type;

use Khalil1608\AdminBundle\Form\DataTransformer\EmailTransformer;
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