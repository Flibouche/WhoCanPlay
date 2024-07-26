<?php

namespace App\Controller\Admin;

use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin-{secret}/game', name: 'app_admin_game_')]
#[IsGranted('ROLE_ADMIN')]
class GameAdminController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'show')]
    public function showGames(string $secret, GameRepository $gameRepository): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        $games = $gameRepository->findBy([], ['id' => 'DESC']);

        return $this->render('admin/games/show.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'games' => $games,
        ]);
    }

    public function deleteGame(string $secret): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->redirectToRoute('app_admin_game_show', ['secret' => $secret]);
    }
}