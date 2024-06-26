<?php

namespace App\Controller;

use App\Entity\Subtype;
use App\Enum\SubtypeState;
use App\Service\IgdbApiService;
use App\Repository\GameRepository;
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
        // J'initialise un tableau vide pour stocker les Subtypes
        $subtypes = [];

        // Je récupère les Subtypes par state
        $notOpenedSubtypes = $subtypeRepository->findBy(['state' => 'Not opened']);
        $pendingSubtypes = $subtypeRepository->findBy(['state' => 'Pending']);
        $processedSubtypes = $subtypeRepository->findBy(['state' => 'Processed']);
        $deniedSubtypes = $subtypeRepository->findBy(['state' => 'Denied']);

        // J'assigne les Subtypes à chaque clé d'état dans $subtypes
        $subtypes['notOpened'] = $notOpenedSubtypes;
        $subtypes['pending'] = $pendingSubtypes;
        $subtypes['processed'] = $processedSubtypes;
        $subtypes['denied'] = $deniedSubtypes;

        // J'initialise un tableau vide pour stocker les Subtypes avec les jeux associés
        $subtypesGames = [];

        // Je fais une boucle foreach pour chaque state de mes Subtypes dans $subtypes
        foreach($subtypes as $state => $subtypeArray) {
            // J'utilise array_map pour traiter chaque Subtype et obtenir les détails du jeu associé
            // Array_map prend deux arguments principaux : 
            // 1. la fonction de rappel (callback) qui sera appliqué à chaque élément du tableau
            // 2. Le tableau à mapper : $subtypes
            $subtypesGames[$state] = array_map(function ($subtype) {
                // Je vérifie que $subtype est bien un objet Subtype
                if (!$subtype instanceof Subtype) {
                    throw new \InvalidArgumentException('Expected an array of Subtype objects.');
                }

                // Je récupère l'ID idGameApi à partir de $subtype
                $idGameApi = $subtype->getIdGameApi();

                // J'utilise le service igdbApiService pour obtenir les informations du jeu en utilisant $idGameApi
                $gameApi = $this->igdbApiService->getGameById($idGameApi);

                // Je retourne un tableau associatif contenant le Subtype et les informations du premier jeu associé
                return [
                    'subtype' => $subtype,
                    'gameApi' => $gameApi ? $gameApi[0] : null, // On utilise le premier élément de $gameApi ou null s'il est vide
                ];
            }, $subtypeArray); // J'applique maintenant la fonction de rappel (callback) à chaque élément de $subtypeArray
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