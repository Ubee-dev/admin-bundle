<?php

namespace Khalil1608\AdminBundle\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[Route(path: '/admin', name: 'security_admin_')]
class AdminLoginController extends AbstractController
{
    public function logout(): mixed
    {
        // controller can be blank: it will never be called!
        throw new Exception('Don\'t forget to activate logout in security.yaml');
    }

    public function login(AuthenticationUtils $authenticationUtils, Environment $twig): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $response = new Response();
        $response->setContent($twig->render('@Khalil1608Admin/Security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]));

        return $response;
    }
}
