<?php

namespace Khalil1608\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminLocaleRedirectController extends AbstractController
{
    private array $supportedLocales = ['en', 'fr'];

    public function redirectToAdmin(Request $request): Response
    {
        // Déterminer la langue préférée de l'utilisateur
        $preferredLocale = $request->getPreferredLanguage($this->supportedLocales);

        // Si la langue préférée n'est pas supportée, utiliser l'anglais par défaut
        if (!$preferredLocale || !in_array($preferredLocale, $this->supportedLocales, true)) {
            $preferredLocale = 'en';
        }

        // Générer l'URL avec la locale détectée
        $url = $this->generateUrl('admin_dashboard', [
            '_locale' => $preferredLocale
        ]);

        // Rediriger vers l'URL localisée
        return $this->redirect($url);
    }
}