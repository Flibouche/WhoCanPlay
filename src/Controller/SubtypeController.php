<?php

namespace App\Controller;

use App\Entity\Subtype;
use App\Form\SubtypeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/subtype', name: 'app_subtype')]
    public function addSubtypeToGame(Subtype $subtype = null, Request $request)
    {

        $form = $this->createForm(SubtypeType::class, $subtype);

        $form->handleRequest($request);

        return $this->render('subtype/index.html.twig', [
            'formAddSubtype' => $form,
        ]);
    }
}
