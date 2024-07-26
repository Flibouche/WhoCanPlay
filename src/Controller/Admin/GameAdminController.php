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

    // Méthode pour créer ou modifier un jeu
    #[Route('/create', name: 'create')]
    #[Route('/edit/{id}', name: 'edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function createOrEditGame(string $secret, ?Game $game, Request $request): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$game) {
            $game = new Game();
        }

        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($game);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_game_show', ['secret' => $secret]);
        }

        return $this->render('admin/games/create.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'formAddGame' => $form,
            'game' => $game,
            'edit' => $game->getId(),
        ]);
    }

    // Méthode pour supprimer un jeu
    #[Route('/delete/{id}', name: 'delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteGame(string $secret): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->redirectToRoute('app_admin_game_show', ['secret' => $secret]);
    }
}
