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
        // Je récupère l'exception
        $exception = $event->getThrowable();

        // Je vérifie si l'exception est de type NotFoundHttpException, autrement je ne fais rien
        if ($exception instanceof NotFoundHttpException) {
            $response = new RedirectResponse($this->router->generate('app_error_404'));
            $event->setResponse($response);
        }
    }
}