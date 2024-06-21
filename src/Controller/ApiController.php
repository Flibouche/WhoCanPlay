<?php

namespace App\Controller;

use App\Service\IgdbApiService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    // Déclaration d'une propriété privée pour le service IgdbApiService
    private $igdbApiService;

    // Constructeur pour injecter le service IgdbApiService
    public function __construct(IgdbApiService $igdbApiService)
    {
        $this->igdbApiService = $igdbApiService;
    }

    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {
        // Appel du service pour récupérer les jeux
        $games = $this->igdbApiService->getGames();

        // Rendu du template 'api/index.html.twig' avec les données des jeux
        return $this->render('api/index.html.twig', [
            'games' => $games,
        ]);
    }

    // #[Route('/search/{name}', name: 'search_api_game')]
    // public function search(Request $request, $name = null): Response
    // {
    //     // Récupère la clé de recherche 'game' depuis la requête
    //     $name = $request->query->get('game');

    //     // Appel du service pour récupérer les jeux basés sur le nom
    //     $games = $this->igdbApiService->getGames($name);

    //     // Rendu du template 'api/search.html.twig' avec les données des jeux
    //     return $this->render('api/search.html.twig', [
    //         'games' => $games,
    //     ]);
    // }

    #[Route('/api/show', name: 'show_api_game')]
    public function getGameDetails(Request $request): Response
    {
        // Récupération des paramètres 'id' et 'name' depuis la requête
        $gameId = $request->query->get('id');
        $gameName = $request->query->get('name');

        // Vérification si l'un des deux paramètres est présent
        if ($gameId === null && $gameName === null) {
            // Retourne une réponse JSON avec une erreur si aucun paramètre n'est fourni
            return $this->json(['error' => 'Either id or name must be provided'], Response::HTTP_BAD_REQUEST);
        }
        try {
            // Appel du service pour récupérer les détails du jeu
            $gameDetails = $this->igdbApiService->getGameDetails($gameId, $gameName);

            // Rendu du template 'api/show.html.twig' avec les détails du jeu
            return $this->render('api/show.html.twig', [
                'gameDetails' => $gameDetails,
            ]);
        } catch (\Exception $e) {
            // En cas d'exception, retourne une réponse JSON avec l'erreur
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}