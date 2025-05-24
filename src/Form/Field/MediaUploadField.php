<?php

namespace Khalil1608\AdminBundle\Form\Field;

use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use Khalil1608\AdminBundle\Form\Type\MediaUploadType;

final class MediaUploadField
{
    /**
     * Crée un nouveau champ pour uploader des médias
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
            ])
            ->setTemplatePath('@Khalil1608Admin/form_theme/media_upload_widget.html.twig');
    }
}