<?php

namespace Khalil1608\AdminBundle\Form\Type;

use Khalil1608\AdminBundle\Form\DataTransformer\UrlTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType as SymfonyUrlType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomUrlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new UrlTransformer());
    }

    public function getParent(): string
    {
        return SymfonyUrlType::class;
    }
}