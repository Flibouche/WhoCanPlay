<?php

namespace App\Controller\Admin;

use App\Entity\Game;
use App\Form\GameType;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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

    // Méthode pour afficher la liste des jeux
    #[Route('/', name: 'show')]
    #[IsGranted('ROLE_ADMIN')]
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

    // Méthode pour afficher les détails d'un jeu
    #[Route('/details/{id}', name: 'details')]
    #[IsGranted('ROLE_ADMIN')]
    public function detailsGame(string $secret, Game $game): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->render('admin/games/details.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'game' => $game,
        ]);
    }

    // Méthode pour supprimer un jeu
    #[Route('/delete/{id}', name: 'delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteGame(string $secret, Game $game): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if(!$game) {
            throw $this->createNotFoundException('Game not found');
        }

        $this->entityManager->remove($game);
        $this->entityManager->flush();

        $this->addFlash('success', 'Game deleted successfully');
        return $this->redirectToRoute('app_admin_game_show', ['secret' => $secret]);
    }
}
