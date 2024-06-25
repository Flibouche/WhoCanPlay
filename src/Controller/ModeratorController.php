<?php

namespace App\Controller;

use App\Repository\SubtypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ModeratorController extends AbstractController
{
    #[Route('/moderator', name: 'app_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function index(SubtypeRepository $subtypeRepository): Response
    {

        $subtypes = $subtypeRepository->findAll();

        return $this->render('moderator/index.html.twig', [
            'subtypes' => $subtypes,
        ]);
    }
}
