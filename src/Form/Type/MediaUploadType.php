<?php

namespace UbeeDev\AdminBundle\Form\Type;

use UbeeDev\LibBundle\Service\MediaManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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

        // Champ pour le fichier
        $builder->add('file', FileType::class, [
            'label' => 'Fichier',
            'required' => false,
            'attr' => [
                'accept' => $options['accept_types'] ?? 'image/*',
                'class' => 'media-upload-file'
            ]
        ]);

        // Champs pour les métadonnées
        if ($options['show_metadata']) {
            $builder->add('alt', TextType::class, [
                'label' => 'Texte alternatif (alt)',
                'required' => false,
                'help' => 'Description de l\'image pour l\'accessibilité',
                'attr' => [
                    'placeholder' => 'Décrivez cette image...',
                    'class' => 'media-upload-alt'
                ]
            ]);

            $builder->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => false,
                'help' => 'Titre affiché au survol de l\'image',
                'attr' => [
                    'placeholder' => 'Titre de l\'image...',
                    'class' => 'media-upload-title'
                ]
            ]);

            if ($options['show_description']) {
                $builder->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'required' => false,
                    'help' => 'Description détaillée (optionnelle)',
                    'attr' => [
                        'rows' => 3,
                        'placeholder' => 'Description détaillée de l\'image...',
                        'class' => 'media-upload-description'
                    ]
                ]);
            }
        }

        // Gérer l'upload post-soumission
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($mediaProperty, $context, $options) {
            $form = $event->getForm();
            $parentForm = $form->getParent();

            if (!$parentForm) {
                return;
            }

            $data = $form->getData();

            // Vérifier s'il y a un nouveau fichier à uploader
            $file = null;
            if (is_array($data) && isset($data['file'])) {
                $file = $data['file'];
            } elseif ($data instanceof UploadedFile) {
                $file = $data;
            }

            if (!$file instanceof UploadedFile) {
                // Pas de nouveau fichier, mais peut-être des métadonnées à mettre à jour
                if ($options['show_metadata'] && is_array($data)) {
                    $this->updateExistingMediaMetadata($parentForm, $mediaProperty, $data);
                }
                return;
            }

            // Upload du nouveau fichier
            $media = $this->mediaManager->upload($file, $context, false, false);

            // Ajouter les métadonnées si présentes
            if ($options['show_metadata'] && is_array($data)) {
                if (isset($data['alt'])) {
                    $media->setAlt($data['alt']);
                }
                if (isset($data['title'])) {
                    $media->setTitle($data['title']);
                }
                if (isset($data['description']) && $options['show_description']) {
                    $media->setDescription($data['description']);
                }
            }

            // Assigner le média à l'entité parente
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
                if ($parentForm->has($mediaProperty)) {
                    $parentForm->get($mediaProperty)->setData($media);
                }
            }
        });
    }

    private function updateExistingMediaMetadata($parentForm, string $mediaProperty, array $data): void
    {
        $parentData = $parentForm->getData();
        $media = null;

        // Récupérer le média existant
        if (is_array($parentData) && isset($parentData[$mediaProperty])) {
            $media = $parentData[$mediaProperty];
        } elseif (is_object($parentData)) {
            $getter = 'get' . ucfirst($mediaProperty);
            if (method_exists($parentData, $getter)) {
                $media = $parentData->$getter();
            }
        }

        // Mettre à jour les métadonnées si le média existe
        if ($media && method_exists($media, 'setAlt')) {
            if (isset($data['alt'])) {
                $media->setAlt($data['alt']);
            }
            if (isset($data['title'])) {
                $media->setTitle($data['title']);
            }
            if (isset($data['description'])) {
                $media->setDescription($data['description']);
            }
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['media_property'] = $options['media_property'];
        $view->vars['show_metadata'] = $options['show_metadata'];
        $view->vars['show_description'] = $options['show_description'];

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
            if (isset($parentData[$mediaProperty])) {
                $media = $parentData[$mediaProperty];
            }
        } elseif (is_object($parentData)) {
            $getter = 'get' . ucfirst($mediaProperty);
            if (method_exists($parentData, $getter)) {
                $media = $parentData->$getter();
            }
        }

        $view->vars['media'] = $media;

        // Préremplir les champs de métadonnées si le média existe
        if ($media && $options['show_metadata']) {
            $formData = $form->getData() ?: [];

            if (!isset($formData['alt']) && method_exists($media, 'getAlt')) {
                $formData['alt'] = $media->getAlt();
            }
            if (!isset($formData['title']) && method_exists($media, 'getTitle')) {
                $formData['title'] = $media->getTitle();
            }
            if (!isset($formData['description']) && method_exists($media, 'getDescription') && $options['show_description']) {
                $formData['description'] = $media->getDescription();
            }

            if (!empty($formData)) {
                $form->setData($formData);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
            'required' => false,
            'media_property' => null,
            'media_context' => 'default',
            'show_metadata' => true,
            'show_description' => false,
            'accept_types' => 'image/*',
            'compound' => true,
        ]);

        $resolver->setRequired(['media_property', 'media_context']);
        $resolver->setAllowedTypes('show_metadata', 'bool');
        $resolver->setAllowedTypes('show_description', 'bool');
    }

    public function getBlockPrefix(): string
    {
        return 'media_upload';
    }
}