<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Feature;
use App\Form\FeatureType;
use App\Enum\FeatureState;
use App\Service\IgdbApiService;
use App\Repository\FeatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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
    public function submissionsBox(FeatureRepository $featureRepository): Response
    {
        // Je définis les états que je veux récupérer
        $states = [FeatureState::NOT_OPENED, FeatureState::PENDING, FeatureState::ACCEPTED, FeatureState::DENIED];

        // J'utilise la méthode findFeaturesByStates pour récupérer les Features par état
        $features = $featureRepository->findFeaturesByStates($states);

        // J'initialise un tableau vide pour stocker les Features avec les jeux associés
        $featuresGames = [
            'notOpened' => [],
            'pending' => [],
            'processed' => [],
            'denied' => [],
        ];

        // Je fais une boucle foreach pour chaque Feature récupéré
        foreach ($features as $feature) {
            // Je récupère l'ID idGameApi à partir de $feature
            $idGameApi = $feature->getIdGameApi();

            // J'utilise le service igdbApiService pour obtenir les informations du jeu en utilisant $idGameApi
            $gameApi = $this->igdbApiService->getGameById($idGameApi);

            // Je détermine l'état du Feature pour le classer correctement
            // Utilisation de match plutôt que de switch pour rendre le code plus lisible
            $stateKey = match ($feature->getState()) {
                FeatureState::NOT_OPENED => 'notOpened',
                FeatureState::PENDING => 'pending',
                FeatureState::ACCEPTED => 'processed',
                FeatureState::DENIED => 'denied',
                default => null,
            };

            // Vérification si $stateKey est différent de null
            if ($stateKey !== null) {
                // J'ajoute le Feature et les informations du jeu associé dans le tableau
                $featuresGames[$stateKey][] = [
                    'feature' => $feature,
                    'gameApi' => $gameApi ? $gameApi[0] : null, // On utilise le premier élément de $gameApi ou null s'il est vide
                ];
            } // TODO : gérer le cas où $stateKey est null
        }

        // Je retourne la vue Twig et je passe le tableau $featuresGames à la vue
        return $this->render('moderator/index.html.twig', [
            'features' => $featuresGames,
        ]);
    }

    #[Route('/moderator/feature/{id}/{slug}', name: 'show_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function showFeature(Feature $feature, EntityManagerInterface $entityManager): Response
    {

        // Dès que j'accède à un Feature, si son state est "Not opened", il passe automatiquement en "Pending"
        if ($feature->getState() == FeatureState::NOT_OPENED) {
            $feature->setState(FeatureState::PENDING);
            $entityManager->persist($feature);
            $entityManager->flush();
        }

        return $this->render('moderator/show.html.twig', [
            'feature' => $feature,
        ]);
    }

    #[Route('/moderator/feature/{id}/{slug}/edit', name: 'edit_feature_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function editFeature(Feature $feature = null, EntityManagerInterface $entityManager, Request $request): Response
    {

        $form = $this->createForm(FeatureType::class, $feature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $feature = $form->getData();

            $entityManager->persist($feature);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('feature/add.html.twig', [
            'formSendFeatureToGame' => $form,
            'edit' => $feature->getId(),
        ]);
    }

    #[Route('/moderator/feature/{id}/{slug}/validate', name: 'validate_feature_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function validateFeature(Feature $feature = null, EntityManagerInterface $entityManager, Request $request): Response
    {
        $feature->setState(FeatureState::ACCEPTED);

        if ($feature->getState() == FeatureState::ACCEPTED) {
            $idGameApi = $feature->getIdGameApi();

            $game = $entityManager->getRepository(Game::class)->findOneBy(['id_game_api' => $idGameApi]);
    
            if (!$game) {
                $game = new Game();
                $game->setIdGameApi($idGameApi);
                $entityManager->persist($game);
                $entityManager->flush();

                $game->getId();
                $feature->setGame($game);
            }

            if ($game->isStatus() == 0) {
                $status = 1;
                $game->setStatus($status);
            }
    
            $entityManager->persist($feature);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_moderator');
        }
    }

    // private function getOrCreateGame(EntityManagerInterface $entityManager, $idGameApi): Game
    // {

    // }

    #[Route('/moderator/feature/{id}/{slug}/deny', name: 'deny_feature_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function denyFeature(Feature $feature = null, EntityManagerInterface $entityManager): Response
    {
        $feature->setState(FeatureState::DENIED);

        $entityManager->persist($feature);
        $entityManager->flush();

        return $this->redirectToRoute('app_moderator');
    }
}