<?php

namespace App\Security;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Redirect404
{
    private $router;

    // Le constructeur prend en paramètre le service de routage pour générer des URL
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    // Méthode appelé lorsqu'une exception se produit
    public function onKernelException(ExceptionEvent $event)
    {
        // Récupère l'exception
        $exception = $event->getThrowable();

        // Vérifie si l'exception est de type NotFoundHttpException (erreur 404)
        if (!$exception instanceof NotFoundHttpException) {
            return; // Si ce n'est pas une erreur 404, ne fait rien
        }

        // Redirection vers la page d'accueil
        $response = new RedirectResponse($this->router->generate('app_home'));

        // Associe la réponse à l'événement
        $event->setResponse($response);
    }
}