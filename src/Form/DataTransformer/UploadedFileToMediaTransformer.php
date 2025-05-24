<?php

namespace Khalil1608\AdminBundle\Form\DataTransformer;


use App\Entity\Media;
use Khalil1608\LibBundle\Service\MediaManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadedFileToMediaTransformer implements DataTransformerInterface
{
    public function __construct(
        private readonly MediaManager $mediaManager,
        private readonly string $context,
        private readonly bool $flush = false
    ) {}

    public function transform($value): ?UploadedFile
    {
        // Affichage du champ (Media → UploadedFile)
        // On retourne null pour laisser le champ vide
        return null;
    }

    public function reverseTransform($value): ?Media
    {

        if (!$value instanceof UploadedFile) {
            return null;
        }
        return $this->mediaManager->upload(
            uploadedFile: $value,
            context: $this->context,
            andFlush: $this->flush
        );
    }
}