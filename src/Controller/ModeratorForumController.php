<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ModeratorForumController extends AbstractController
{
    #[Route('/moderator/forum', name: 'app_moderator_forum')]
    public function index(): Response
    {
        return $this->render('moderator_forum/index.html.twig', [
            'controller_name' => 'ModeratorForumController',
        ]);
    }
}
