<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GoogleController extends AbstractController
{
    // Je vais sur le client Google de connexion grâce à cette méthode
    #[Route('/connect/google', name: 'connect_google')]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect([], []);
    }

    // Je récupère les informations de l'utilisateur Google et je le connecte
    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(Request $request, EventDispatcherInterface $eventDispatcher)
    {
        // L'utilisateur est déjà authentifié à ce stade, grâce à l'authenticator.
        
        // Vous pouvez maintenant déclencher un événement d'interaction utilisateur, si nécessaire.
        $token = $this->getUser();  // Récupère l'utilisateur authentifié.

        // Créez un événement pour l'authentification interactive.
        $event = new InteractiveLoginEvent($request, $token);
        $eventDispatcher->dispatch($event);

        // Redirigez l'utilisateur vers la page d'accueil ou une autre page.
        return $this->redirectToRoute('app_home');
    }
}
