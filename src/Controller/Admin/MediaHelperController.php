<?php

namespace UbeeDev\AdminBundle\Controller\Admin;

use App\Entity\Media;
use UbeeDev\LibBundle\Service\MediaManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur helper pour servir les médias dans l'interface d'administration
 */
class MediaHelperController extends AbstractController
{
    public function __construct(
        private readonly MediaManager $mediaManager
    ) {
    }

    #[Route('/admin/media/{id}/web-path', name: 'admin_media_web_path')]
    public function getWebPath(Media $media): Response
    {
        try {
            if ($media->isPrivate()) {
                return new Response('', Response::HTTP_FORBIDDEN);
            }

            $webPath = $this->mediaManager->getWebPath($media);

            // Retourner l'image directement pour la prévisualisation
            if ($media->isImage()) {
                $filePath = $this->mediaManager->getRelativePath($media);
                if (file_exists($filePath)) {
                    return new Response(
                        file_get_contents($filePath),
                        Response::HTTP_OK,
                        [
                            'Content-Type' => $media->getContentType(),
                            'Cache-Control' => 'public, max-age=3600',
                        ]
                    );
                }
            }

            return new Response($webPath);
        } catch (\Exception $e) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }
    }
}