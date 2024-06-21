<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SubtypeController extends AbstractController
{
    #[Route('/subtype', name: 'app_subtype')]
    public function index(): Response
    {
        return $this->render('subtype/index.html.twig', [
            'controller_name' => 'SubtypeController',
        ]);
    }
}
