<?php

namespace UbeeDev\AdminBundle\Form\Field;

use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use UbeeDev\AdminBundle\Form\Type\MediaUploadType;

final class MediaUploadField
{
    /**
     * Crée un nouveau champ pour uploader des médias avec métadonnées
     *
     * @param string $formField Le nom du champ du formulaire (avec le suffixe "Upload")
     * @param string|null $label Le libellé du champ
     * @param string $mediaContext Le contexte du média (pour le stockage)
     * @return Field
     */
    public static function new(string $formField, ?string $label = null, string $mediaContext = 'default'): Field
    {
        // Ex: headerImageUpload → headerImage
        $entityProperty = preg_replace('/Upload$/', '', $formField);

        return Field::new($formField, $label)
            ->setFormType(MediaUploadType::class)
            ->setFormTypeOptions([
                'media_property' => $entityProperty,
                'media_context' => $mediaContext,
                'show_metadata' => true,
                'show_description' => false,
                'accept_types' => 'image/*',
            ])
            ->setTemplatePath('@UbeeDevAdmin/form_theme/media_upload_widget.html.twig');
    }

    /**
     * Crée un champ media upload avec description complète
     */
    public static function withDescription(string $formField, ?string $label = null, string $mediaContext = 'default'): Field
    {
        return self::new($formField, $label, $mediaContext)
            ->setFormTypeOption('show_description', true);
    }

    /**
     * Crée un champ media upload sans métadonnées (comportement original)
     */
    public static function simple(string $formField, ?string $label = null, string $mediaContext = 'default'): Field
    {
        return self::new($formField, $label, $mediaContext)
            ->setFormTypeOption('show_metadata', false);
    }

    /**
     * Crée un champ pour tous types de fichiers (pas seulement images)
     */
    public static function forAllFiles(string $formField, ?string $label = null, string $mediaContext = 'default'): Field
    {
        return self::new($formField, $label, $mediaContext)
            ->setFormTypeOption('accept_types', '*/*')
            ->setFormTypeOption('show_metadata', false); // Pas de métadonnées pour les fichiers non-image
    }

    /**
     * Crée un champ spécifiquement pour les vidéos
     */
    public static function forVideo(string $formField, ?string $label = null, string $mediaContext = 'default'): Field
    {
        return self::new($formField, $label, $mediaContext)
            ->setFormTypeOption('accept_types', 'video/*')
            ->setFormTypeOption('show_description', true);
    }
}