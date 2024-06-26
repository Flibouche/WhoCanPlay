<?php

namespace App\Controller;

use App\Entity\Subtype;
use App\Enum\SubtypeState;
use App\Service\IgdbApiService;
use App\Repository\SubtypeRepository;
use Doctrine\ORM\EntityManagerInterface;
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
        // Je définis les états que je veux récupérer
        $states = [SubtypeState::NOT_OPENED, SubtypeState::PENDING, SubtypeState::ACCEPTED, SubtypeState::DENIED];

        // J'utilise la méthode findSubtypesByStates pour récupérer les Subtypes par état
        $subtypes = $subtypeRepository->findSubtypesByStates($states);
        
        // J'initialise un tableau vide pour stocker les Subtypes avec les jeux associés
        $subtypesGames = [
            'notOpened' => [],
            'pending' => [],
            'processed' => [],
            'denied' => [],
        ];
        
        // Je fais une boucle foreach pour chaque Subtype récupéré
        foreach ($subtypes as $subtype) {
            // Je récupère l'ID idGameApi à partir de $subtype
            $idGameApi = $subtype->getIdGameApi();
            
            // J'utilise le service igdbApiService pour obtenir les informations du jeu en utilisant $idGameApi
            $gameApi = $this->igdbApiService->getGameById($idGameApi);
            
            // Je détermine l'état du Subtype pour le classer correctement
            // Utilisation de match plutôt que de switch pour rendre le code plus lisible
            $stateKey = match ($subtype->getState()) {
                SubtypeState::NOT_OPENED => 'notOpened',
                SubtypeState::PENDING => 'pending',
                SubtypeState::ACCEPTED => 'processed',
                SubtypeState::DENIED => 'denied',
                default => null,
            };

            // Vérification si $stateKey est différent de null
            if ($stateKey !== null) {
                // J'ajoute le Subtype et les informations du jeu associé dans le tableau
                $subtypesGames[$stateKey][] = [
                    'subtype' => $subtype,
                    'gameApi' => $gameApi ? $gameApi[0] : null, // On utilise le premier élément de $gameApi ou null s'il est vide
                ];
            } // TODO : gérer le cas où $stateKey est null
        }

        // Je retourne la vue Twig et je passe le tableau $subtypesGames à la vue
        return $this->render('moderator/index.html.twig', [
            'subtypes' => $subtypesGames,
        ]);
    }

    #[Route('/moderator/show/{id}', name: 'show_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function show(Subtype $subtype, EntityManagerInterface $entityManager): Response
    {
        // if($subtype->getState() == "Not opened") {
        //     $pendingState = "Pending";
        //     $subtype->setState($pendingState);
        //     $entityManager->persist($subtype);
        //     $entityManager->flush();
        // }

        return $this->render('moderator/show.html.twig', [
            'subtype' => $subtype,
        ]);
    }
}
