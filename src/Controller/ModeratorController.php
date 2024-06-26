<?php

namespace App\Controller;

use App\Entity\Subtype;
use App\Enum\SubtypeState;
use App\Service\IgdbApiService;
use App\Repository\GameRepository;
use App\Repository\SubtypeRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ModeratorController extends AbstractController
{

    private $igdbApiService;

    public function __construct(IgdbApiService $igdbApiService)
    {
        $this->igdbApiService = $igdbApiService;
    }

    #[Route('/moderator', name: 'app_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function index(SubtypeRepository $subtypeRepository): Response
    {
        // Je récupère tous les enregistrements de la table Subtype
        $subtypes = $subtypeRepository->findAll();

        // J'utilise array_map qui me permet de créer un tableau contenant les subtypes et les jeux associés
        // Array_map prend deux arguments principaux :
        // 1. La fonction de rappel (callback) qui sera appliquée à chaque élément du tableau
        // 2. Le tableau à mapper : $subtypes
        $subtypesGames = array_map(function ($subtype) {

            // Je récupère l'ID idGameApi à partir de mon subtype
            $idGameApi = $subtype->getIdGameApi();

            // J'utilise le service igdbApiService pour obtenir les informations du jeu en utilisant $idGameApi
            $gameApi = $this->igdbApiService->getGameById($idGameApi);

            // Je retourne un tableau associatif qui contient le subtype et le premier élément de gameApi (ou null si gameApi est vide)
            return [
                'subtype' => $subtype,
                'gameApi' => $gameApi[0] ?? null,
            ];
        }, $subtypes);

        // Je retourne la vue Twig et je passe le tableau subtypesGames à la vue
        return $this->render('moderator/index.html.twig', [
            'subtypes' => $subtypesGames,
        ]);
    }

    #[Route('/moderator/show/{id}', name: 'show_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function show(Subtype $subtype): Response
    {

        return $this->render('moderator/show.html.twig', [
            'subtype' => $subtype,
        ]);
    }
}