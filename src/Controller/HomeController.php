<?php

namespace App\Controller;

use App\Service\IgdbApiService;
use App\Repository\GameRepository;
use App\Repository\FeatureRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class HomeController extends AbstractController
{
    public function __construct(private IgdbApiService $igdbApiService, private GameRepository $gameRepository, private FeatureRepository $featureRepository) {}

    #region Home
    // Méthode pour afficher la page d'accueil
    #[Route('/home', name: 'app_home')]
    #[Cache(public: true, maxage: 3600, mustRevalidate: true)]
    public function home(): Response
    {
        // J'appelle la méthode privée showGames pour afficher les 3 derniers jeux ajoutés
        $games = $this->showGames(3);

        // J'appelle la méthode privée showFeatures pour afficher les 3 dernières features ajoutées 
        $features = $this->showFeatures(3);

        // Je retourne la vue home.html.twig avec les jeux
        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
            'games' => $games,
            'features' => $features,
        ]);
    }

    // Méthode pour afficher les jeux
    private function showGames(int $limit): array
    {
        // Je cherche directement les jeux ayant le statut 1 (actifs), les range par ID décroissante, et je limite les résultats selon le paramètre $limit
        $activeGames = $this->gameRepository->findBy(['status' => 1], ['id' => 'DESC'], $limit);

        // Si je ne trouve aucun jeu actif, j'ajoute un message flash d'avertissement "No game found!" et retourne un tableau vide
        if (empty($activeGames)) {
            $this->addFlash('warning', "No game found!");
            return [];
        }

        // Je crée un tableau des ID de l'API de jeux en utilisant la méthode array_map pour extraire les ID de chaque jeu actif
        $gameApiIds = array_map(fn($game) => $game->getIdGameApi(), $activeGames);

        // Je récupère les données des jeux depuis l'API IGDB en utilisant les ID que j'ai obtenus
        $gamesApiData = $this->igdbApiService->getGameByIds($gameApiIds);

        // Je crée un tableau indexé par les ID des jeux pour faciliter l'accès aux données des jeux API
        $gamesApiDataById = array_column($gamesApiData, null, 'id');

        // Je combine les données des jeux de ma base de données avec celles de l'API.
        // Pour chaque jeu actif, je retourne un tableau associatif contenant les données de ma base de données et de l'API
        return array_map(function ($gameDb) use ($gamesApiDataById) {
            return [
                'db' => $gameDb,
                'api' => $gamesApiDataById[$gameDb->getIdGameApi()] ?? null,
            ];
        }, $activeGames);
    }

    // Méthode privée pour afficher les features
    private function showFeatures(int $limit): array
    {
        $features = $this->featureRepository->findBy(['state' => 'Processed'], ['id' => 'DESC'], $limit);

        return $features;
    }
    #endregion

    #region Legal
    // Méthodes pour afficher les pages légales
    #[Route('/home/accessibility-statement', name: 'app_accessibility_statement')]
    public function accessibilityStatement(): Response
    {

        return $this->render('home/accessibilityStatement.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    // Méthode pour afficher la page de FAQ
    #[Route('/faq', name: 'app_faq')]
    public function faq(): Response
    {

        return $this->render('home/faq.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    // Méthode pour afficher la page d'aide
    #[Route('/help', name: 'app_help')]
    public function help(): Response
    {

        return $this->render('home/help.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    // Méthode pour afficher la page de politique de confidentialité
    #[Route('/privacy-policy', name: 'app_privacy_policy')]
    public function privacyPolicy(): Response
    {

        return $this->render('home/privacyPolicy.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    // Méthode pour afficher la page de plan du site
    #[Route('/site-map', name: 'app_sitemap')]
    public function siteMap(): Response
    {

        return $this->render('home/siteMap.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    // Méthode pour afficher la page de conditions d'utilisation
    #[Route('/terms-and-conditions', name: 'app_terms_and_conditions')]
    public function termsAndConditions(): Response
    {

        return $this->render('home/termsAndConditions.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
    #endregion

    #[Route('/admin', name: 'redirect')]
    public function redirectToRickRoll(): Response
    {
        return $this->redirect('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
    }

    #[Route('/searchGame', name: 'app_search_game', methods: ['GET'])]
    public function search(GameRepository $gameRepository, Request $request): JsonResponse
    {
        $key = $request->query->get('search', '');

        $games = $gameRepository->findByWord($key);

        $gamesContent = array_map(function ($game) {
            return [
                'id' => $game->getId(),
                'name' => $game->getName(),
                'imageUrl' => $game->getImageUrl(),
                'slug' => $game->getSlug(),
            ];
        }, $games);

        return new JsonResponse($gamesContent);
    }
}
