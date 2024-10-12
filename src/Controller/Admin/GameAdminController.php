<?php

namespace App\Controller\Admin;

use App\Entity\Game;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/admin-{secret}/game', name: 'app_admin_game_')]
#[IsGranted('ROLE_ADMIN')]
class GameAdminController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private string $admin_secret)
    {
    }

    // Méthode pour afficher la liste des jeux
    #[Route('/', name: 'show')]
    #[IsGranted('ROLE_ADMIN')]
    public function showGames(GameRepository $gameRepository): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
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
    public function detailsGame(Game $game): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
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
    public function deleteGame(Game $game, CsrfTokenManagerInterface $csrfTokenManager, Request $request): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if(!$game) {
            throw $this->createNotFoundException('Game not found');
        }

        $token = new CsrfToken('delete_item', $request->request->get('_token'));

        if (!$csrfTokenManager->isTokenValid($token)) {
            throw $this->createAccessDeniedException('Token not valid');
        }

        $this->entityManager->remove($game);
        $this->entityManager->flush();

        $this->addFlash('success', 'Game deleted successfully');
        return $this->redirectToRoute('app_admin_game_show', ['secret' => $this->admin_secret]);
    }
}
