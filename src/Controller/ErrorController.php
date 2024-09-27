<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/error', name: 'app_error_')]
class ErrorController extends AbstractController
{
    #[Route('/403', name: '403')]
    public function error403(): Response
    {
        return $this->render('error/403.html.twig');
    }

    #[Route('/404', name: '404')]
    public function error404(): Response
    {
        return $this->render('error/404.html.twig');
    }

    #[Route('/500', name: '500')]
    public function error500(): Response
    {
        return $this->render('error/500.html.twig');
    }
}
