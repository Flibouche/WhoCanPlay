<?php

namespace App\Security;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Redirect403
{
    // Le constructeur prend en paramètre le service de routage pour générer des URL
    public function __construct(private RouterInterface $router) {}

    // Méthode appelé lorsqu'une exception se produit
    public function onKernelException(ExceptionEvent $event)
    {
        // Je récupère l'exception
        $exception = $event->getThrowable();

        // Je vérifie si l'exception est de type AccessDeniedHttpException, autrement je ne fais rien
        if ($exception instanceof AccessDeniedHttpException) {
            $response = new RedirectResponse($this->router->generate('app_error_403'));
            $event->setResponse($response);
        }
    }
}
