<?php

namespace App\Controller;

use App\Entity\Game;
use App\Service\IgdbApiService;
use App\Repository\GameRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GameController extends AbstractController
{

    private $igdbApiService;

    public function __construct(IgdbApiService $igdbApiService)
    {
        $this->igdbApiService = $igdbApiService;
    }

    #[Route('/game', name: 'app_game')]
    public function index(GameRepository $gameRepository): Response
    {
        // Je cherche directement les jeux ayant le status 1, soit les jeux actifs.
        $activeGames = $gameRepository->findBy(['status' => 1]);

        $gamesApiInfo = [];

        if ($activeGames) {
            foreach ($activeGames as $gameDb) {
                $idGameApi = $gameDb->getIdGameApi();
                
                $gameApi = $this->igdbApiService->getGameById($idGameApi);
                
                $gamesApiInfo[] = [
                    'db' => $gameDb,
                    'api' => $gameApi,
                ];
            }
        } else {
            $this->addFlash('warning', "No game found.");
        }

        return $this->render('game/index.html.twig', [
            'games' => $gamesApiInfo,
        ]);
    }

    #[Route('/game/{id}', name: 'show_game')]
    public function showGame(Game $game = null): Response
    {

        $subtypesByDisability = [];

        foreach ($game->getSubtypes() as $subtype) {
            $disability = $subtype->getDisability();
            $disabilityName = $disability ? $disability->getName() : 'No Disability';

            if (!isset($subtypesByDisability[$disabilityName])) {
                $subtypesByDisability[$disabilityName] = [];
            }
            $subtypesByDisability[$disabilityName][] = $subtype;
        }

        $idGameApi = $game->getIdGameApi();

        $gameApi = $this->igdbApiService->getGameDetails($idGameApi);

        return $this->render('game/show.html.twig', [
            'game' => $game,
            'gameApi' => $gameApi,
            'subtypesByDisability' => $subtypesByDisability,
        ]);
    }
}
