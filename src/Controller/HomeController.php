<?php

namespace App\Controller;

use App\Repository\GameRepository;
use App\Service\IgdbApiService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    private $igdbApiService;
    private $gameRepository;

    public function __construct(IgdbApiService $igdbApiService, GameRepository $gameRepository)
    {
        $this->igdbApiService = $igdbApiService;
        $this->gameRepository = $gameRepository;
    }

    #[Route('/home', name: 'app_home')]
    #[Cache(public: true, maxage: 3600, mustRevalidate: true)]
    public function home(): Response
    {
        $gamesApiInfo = $this->showGames(3);

        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
            'games' => $gamesApiInfo,
        ]);
    }

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
        return array_map(function($gameDb) use ($gamesApiDataById) {
            return [
                'db' => $gameDb,
                'api' => $gamesApiDataById[$gameDb->getIdGameApi()] ?? null,
            ];
        }, $activeGames);
    }

    #[Route('/home/accessibility-statement', name: 'app_accessibility_statement')]
    public function accessibilityStatement(): Response
    {

        return $this->render('home/accessibilityStatement.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {

        return $this->render('home/contact.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/help', name: 'app_help')]
    public function help(): Response
    {

        return $this->render('home/help.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/privacy-policy', name: 'app_privacy_policy')]
    public function privacyPolicy(): Response
    {

        return $this->render('home/privacyPolicy.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/site-map', name: 'app_sitemap')]
    public function siteMap(): Response
    {

        return $this->render('home/siteMap.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    #[Route('/terms-and-conditions', name: 'app_terms_and_conditions')]
    public function termsAndConditions(): Response
    {

        return $this->render('home/termsAndConditions.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

}