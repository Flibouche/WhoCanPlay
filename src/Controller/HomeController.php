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

    public function __construct(IgdbApiService $igdbApiService)
    {
        $this->igdbApiService = $igdbApiService;
    }

    #[Route('/home', name: 'app_home')]
    #[Cache(public: true, maxage: 3600, mustRevalidate: true)]
    public function index(GameRepository $gameRepository): Response
    {
        // Je cherche directement les jeux ayant le status 1, soit les jeux actifs, je les range par ID la plus récente et je limite à 3
        $activeGames = $gameRepository->findBy(['status' => 1], ['id' => 'DESC'], 3);

        // Je crée un tableau vide pour y stocker les IdGameApi
        $gameApiIds = [];
        foreach ($activeGames as $game) {
            $gameApiIds[] = $game->getIdGameApi();
        }

        // Avec mon nouveau tableau, je récupère les informations via le service de l'API en y passant mon tableau en argument
        $gamesApiData = $this->igdbApiService->getGameByIds($gameApiIds);

        // Je crée un nouveau tableau pour pouvoir indexer les jeux par leur ID correspondante grâce aux ID que j'ai récupéré de mon service 
        $gamesApiDataById = [];
        foreach ($gamesApiData as $game) {
            $gamesApiDataById[$game['id']] = $game;
        }

        // Je crée un tableau vide pour y stocker les informations combinées des jeux de ma BDD et des jeux de l'API
        $gamesApiInfo = [];

        // Je vérifie si la liste des jeux actifs n'est pas vide
        if ($activeGames) {
            foreach ($activeGames as $gameDb) {
                // Pour chaque jeu actif, je récupère l'idGameApi
                $idGameApi = $gameDb->getIdGameApi();

                // Je récupère les informations du jeu à partir du tableau indexé que j'ai crée plus haut
                $gameApi = $gamesApiDataById[$idGameApi] ?? null;

                // J'ajoute un tableau associatif à mon tableau $gamesApiInfo qui contient maintenant les informations combinées des jeux de ma BDD et des jeux de l'API
                $gamesApiInfo[] = [
                    'db' => $gameDb,
                    'api' => $gameApi,
                ];
            }
        } else {
            // Si aucun jeu actif n'est trouvé, j'ajoute un message flash "No game found !"
            $this->addFlash('warning', "No game found !");
        }

        return $this->render('home/index.html.twig', [
            'games' => $gamesApiInfo,
            'controller_name' => 'HomeController',
        ]);
    }
}
