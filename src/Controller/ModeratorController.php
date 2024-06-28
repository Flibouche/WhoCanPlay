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
use Symfony\Component\String\Slugger\AsciiSlugger;

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

        // Dès que j'accède à un objet Feature, si son State est "Not opened", il passe automatiquement en "Pending"
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
    public function validateFeature(Feature $feature = null, EntityManagerInterface $entityManager): Response
    {

        // Je vérifie si la feature est null et je lance une exception si c'est le cas
        if (!$feature) {
            throw $this->createNotFoundException('Feature not found');
        }

        try {
            // Je commence une transaction pour garantir l'atomicité des opérations pour l'ajout en BDD
            // Atomicité : ensemble d'opérations d'un programme qui s'exécutent entièrement sans pouvoir être interrompues avant la fin de leur déroulement
            $entityManager->beginTransaction();

            // Je change le State de mon objet Feature à ACCEPTED
            $feature->setState(FeatureState::ACCEPTED);

            // Je vérifie si le State de mon objet Feature a bien été changé en ACCEPTED
            if ($feature->getState() == FeatureState::ACCEPTED) {
                // Je récupère ou je crée le jeu associé à l'IDGameApi de mon objet Feature
                $game = $this->getOrCreateGame($entityManager, $feature->getIdGameApi());

                // Je vérifie si le Statut de mon objet Game est false et je le change en true 
                if (!$game->isStatus()) {
                    $game->setStatus(true);
                    // Je prépare mes données à être insérées dans mon objet Game
                    $entityManager->persist($game);
                }

                // J'associe enfin mon objet Game à mon objet Feature
                $feature->setGame($game);
                // Je prépare mes données à être insérées dans mon objet Feature
                $entityManager->persist($feature);
            }

            // Je flush mes opérations en attente (persist) vers ma base de données
            $entityManager->flush();
            // J'envoie la transaction
            $entityManager->commit();
        } catch (\Exception $e) {
            // En cas d'exception, j'annule toutes les opérations de la transaction
            $entityManager->rollback();
            throw $e;
        }

        // Je redirige l'utilisateur vers la route 'app_moderator' après la validation des opérations
        return $this->redirectToRoute('app_moderator');
    }

    // Méthode privée pour obtenir ou créer un jeu basé sur l'ID de l'API du jeu
    private function getOrCreateGame(EntityManagerInterface $entityManager, $idGameApi): Game
    {
        // Je cherche le jeu dans ma base de données en utilisant l'ID de l'API du jeu
        $game = $entityManager->getRepository(Game::class)->findOneBy(['id_game_api' => $idGameApi]);
        
        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($this->igdbApiService->getGameById($idGameApi)[0]["name"]);

        // Si aucun jeu n'est trouvé, je crée un nouveau jeu
        if (!$game) {
            $game = new Game();
            $game->setIdGameApi($idGameApi);
            // TODO : enlever le strval ? Enlever la création du slug pour mon jeu et récupérer directement le slug de l'API ?
            $game->setSlug(strval($slug));
            $entityManager->persist($game);
            $entityManager->flush();
        }

        // Je retourne le jeu trouvé ou crée
        return $game;
    }

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
