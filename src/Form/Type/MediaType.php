<?php

namespace UbeeDev\AdminBundle\Form\Type;

use UbeeDev\LibBundle\Entity\Media;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class MediaType extends AbstractType
{
    public function __construct(
        private readonly ParameterBagInterface $parameterBag
    )
    {

    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'admin.media.choose_file',
                'required' => false,
                'mapped' => false,
            ])
            ->add('id', HiddenType::class, [
                'required' => false,
                'mapped' => false,
            ]);

        // Listen to PRE_SET_DATA event to handle existing Media
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $media = $event->getData();
            $form = $event->getForm();

            if ($media && $media->getId()) {
                $form->get('id')->setData($media->getId());
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => $this->parameterBag->get('mediaClassName'),
            'translation_domain' => 'admin',
        ]);
    }
}
