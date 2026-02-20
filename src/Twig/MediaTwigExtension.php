<?php

namespace UbeeDev\AdminBundle\Twig;

use App\Entity\Media;
use UbeeDev\LibBundle\Service\MediaManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MediaTwigExtension extends AbstractExtension
{
    public function __construct(
        private readonly MediaManager $mediaManager
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('media_url', [$this, 'getMediaUrl']),
            new TwigFunction('media_path', [$this, 'getMediaPath']),
            new TwigFunction('format_file_size', [$this, 'formatFileSize']),
        ];
    }

    /**
     * Obtient l'URL web d'un média
     */
    public function getMediaUrl(Media $media): ?string
    {
        try {
            if ($media->isPrivate()) {
                return null;
            }
            return $this->mediaManager->getWebPath($media);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtient le chemin relatif d'un média
     */
    public function getMediaPath(Media $media): string
    {
        return $this->mediaManager->getRelativePath($media);
    }

    /**
     * Formate la taille d'un fichier de manière lisible
     */
    public function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor(log($bytes, 1024));

        return sprintf('%.1f %s', $bytes / pow(1024, $factor), $units[$factor]);
    }
}