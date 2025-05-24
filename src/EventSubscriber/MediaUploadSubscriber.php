<?php

namespace Khalil1608\AdminBundle\EventSubscriber;

use Khalil1608\LibBundle\Service\MediaManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaUploadSubscriber implements EventSubscriberInterface
{
    public function __construct(private MediaManager $mediaManager) {}

    public static function getSubscribedEvents(): array
    {
        return [FormEvents::POST_SUBMIT => 'handleUpload'];
    }

    public function handleUpload(FormEvent $event): void
    {
        $form = $event->getForm();
        $entity = $event->getData();

        foreach ($form as $child) {
            $field = $child->getConfig()->getOption('ea_field');
            if (!$field) continue;

            $targetProp = $field->getCustomOption('media_property');
            if (!$targetProp) continue;

            $file = $child->getData();
            if (!$file instanceof UploadedFile) continue;

            $media = $this->mediaManager->upload($file, $entity::MEDIA_CONTEXT);
            $setter = 'set' . ucfirst($targetProp);

            if (method_exists($entity, $setter)) {
                $entity->$setter($media);
            }
        }
    }

}
