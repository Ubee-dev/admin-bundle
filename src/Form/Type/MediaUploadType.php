<?php

namespace Khalil1608\AdminBundle\Form\Type;

use Khalil1608\LibBundle\Service\MediaManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class MediaUploadType extends AbstractType
{
    private readonly MediaManager $mediaManager;

    public function __construct(MediaManager $mediaManager)
    {
        $this->mediaManager = $mediaManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $mediaProperty = $options['media_property'];
        $context = $options['media_context'];

        // Gérer l'upload post-soumission
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($mediaProperty, $context) {
            $form = $event->getForm();
            $parentForm = $form->getParent();

            if (!$parentForm) {
                return;
            }

            $file = $form->getData();
          
            if (!$file instanceof UploadedFile) {
                return;
            }

            $media = $this->mediaManager->upload($file, $context, false, false);

            // Cas 1 : les données sont disponibles
            $parentData = $parentForm->getData();


            if ($parentData !== null) {
                if (is_array($parentData)) {
                    $parentData[$mediaProperty] = $media;
                    $parentForm->setData($parentData);
                } elseif (is_object($parentData)) {
                    $setter = 'set' . ucfirst($mediaProperty);
                    if (method_exists($parentData, $setter)) {
                        $parentData->$setter($media);
                    }
                }
            } else {
                // Cas 2 : impossible de setter via les données => setter via la structure du formulaire
                if ($parentForm->has($mediaProperty)) {
                    $parentForm->get($mediaProperty)->setData($media);
                }
            }
        });

    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['media_property'] = $options['media_property'];

        // Récupérer l'entité ou les données pour afficher le média existant
        $parentForm = $form->getParent();
        if (!$parentForm) {
            return;
        }

        $parentData = $parentForm->getData();
        $mediaProperty = $options['media_property'];
        $media = null;


        // Vérifier explicitement le type de parentData
        if (is_array($parentData)) {
            // Pour les données sous forme de tableau
            if (isset($parentData[$mediaProperty])) {
                $media = $parentData[$mediaProperty];
            }
        } elseif (is_object($parentData)) {
            // Pour les objets avec getters
            $getter = 'get' . ucfirst($mediaProperty);
            if (method_exists($parentData, $getter)) {
                $media = $parentData->$getter();
            }
        }

        $view->vars['media'] = $media;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
            'required' => false,
            'media_property' => null,
            'media_context' => 'default',
        ]);

        $resolver->setRequired(['media_property', 'media_context']);
    }

    public function getBlockPrefix(): string
    {
        return 'media_upload';
    }

    public function getParent(): string
    {
        return FileType::class;
    }
}