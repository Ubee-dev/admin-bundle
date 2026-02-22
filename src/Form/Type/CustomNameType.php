<?php

namespace UbeeDev\AdminBundle\Form\Type;

use UbeeDev\AdminBundle\Form\DataTransformer\NameTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType as SymfonyNameType;
use Symfony\Component\Form\FormBuilderInterface;

class CustomNameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new NameTransformer());
    }

    public function getParent(): string
    {
        return SymfonyNameType::class;
    }
}