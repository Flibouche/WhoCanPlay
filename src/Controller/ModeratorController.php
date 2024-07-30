<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Feature;
use App\Enum\FeatureState;
use App\Service\IgdbApiService;
use App\Repository\FeatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ModeratorController extends AbstractController
{

    private $igdbApiService;
    private $entityManager;

    public function __construct(IgdbApiService $igdbApiService, EntityManagerInterface $entityManager)
    {
        $this->igdbApiService = $igdbApiService;
        $this->entityManager = $entityManager;
    }

    #region Submission Box
    // Méthode pour afficher les features qui ont été soumises
    #[Route('/moderator', name: 'app_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function submissionsBox(FeatureRepository $featureRepository): Response
    {
        // Je définis les états que je veux récupérer
        $states = [FeatureState::NOT_OPENED, FeatureState::PENDING, FeatureState::PROCESSED, FeatureState::DENIED];

        // J'utilise la méthode findFeaturesByStates pour récupérer les Features par State
        $features = $featureRepository->findFeaturesByStates($states);

        // J'extrait les ID des jeux associés aux Features et j'enlève les doublons avec array_unique
        // Je crée donc un tableau d'ID unique à partir des Features
        $gameApiIds = array_unique(array_map(fn ($feature) => $feature->getIdGameApi(), $features));

        // J'utilise le service pour obtenir les informations des jeux en utilisant les ID que j'ai récupéré plus haut
        $gamesApiData = $this->igdbApiService->getGameByIds($gameApiIds);

        // Je crée un nouveau tableau pour pouvoir indexer les jeux par leur ID correspondante grâce aux ID que j'ai récupérés du service 
        $gamesApiDataById = [];
        foreach ($gamesApiData as $game) {
            $gamesApiDataById[$game['id']] = $game;
        }

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

            // Je détermine l'état du Feature pour le classer correctement
            // Utilisation de match plutôt que de switch pour rendre le code plus lisible
            $stateKey = match ($feature->getState()) {
                FeatureState::NOT_OPENED => 'notOpened',
                FeatureState::PENDING => 'pending',
                FeatureState::PROCESSED => 'processed',
                FeatureState::DENIED => 'denied',
                default => null,
            };

            // Vérification si $stateKey est différent de null
            if ($stateKey !== null) {
                // Je récupère les informations du jeu à partir du tableau indexé que j'ai crée plus haut
                $gameApi = $gamesApiDataById[$idGameApi] ?? null;

                // J'ajoute le Feature et les informations du jeu associé dans le tableau
                $featuresGames[$stateKey][] = [
                    'feature' => $feature,
                    'gameApi' => $gameApi,
                ];
            } // TODO : gérer le cas où $stateKey est null
        }

        // Je retourne la vue Twig et je passe le tableau $featuresGames à la vue
        return $this->render('moderator/submissionBox.html.twig', [
            'controller_name' => 'ModeratorController',
            'features' => $featuresGames,
        ]);
    }
    #endregion

    #region Show Feature
    // Méthode pour afficher une feature
    #[Route('/moderator/feature/{id}/{slug}', name: 'show_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function showFeature(Feature $feature): Response
    {
        // Je récupère l'entity manager
        $em = $this->entityManager;

        // Dès que j'accède à un objet Feature, si son State est "Not opened", il passe automatiquement en "Pending"
        if ($feature->getState() == FeatureState::NOT_OPENED) {
            $feature->setState(FeatureState::PENDING);
            $em->persist($feature);
            $em->flush();
        }

        // Je récupère les informations du jeu associé à l'ID de l'API du jeu
        $idGameApi = $feature->getIdGameApi();
        $gameApiData = $this->igdbApiService->getGameById($idGameApi);

        // Je retourne la vue Twig et je passe les informations du Feature et du jeu à la vue
        return $this->render('moderator/showFeature.html.twig', [
            'controller_name' => 'ModeratorController',
            'feature' => $feature,
            'gameApi' => $gameApiData,
        ]);
    }

    // Méthode pour valider une feature
    #[Route('/moderator/feature/{id}/{slug}/validate', name: 'validate_feature_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function validateFeature(?Feature $feature): Response
    {
        // Je récupère l'entity manager
        $em = $this->entityManager;

        // Je vérifie si la feature est null et je lance une exception si c'est le cas
        if (!$feature) {
            throw $this->createNotFoundException('Feature not found');
        }

        // Je commence une transaction pour garantir l'atomicité des opérations pour l'ajout en BDD
        // Atomicité : ensemble d'opérations d'un programme qui s'exécutent entièrement sans pouvoir être interrompues avant la fin de leur déroulement
        $em->beginTransaction();
        try {

            // Je change le State de mon objet Feature à ACCEPTED
            $feature->setState(FeatureState::PROCESSED);

            // Je vérifie si le State de mon objet Feature a bien été changé en ACCEPTED
            if ($feature->getState() == FeatureState::PROCESSED) {
                // Je récupère ou je crée le jeu associé à l'IDGameApi de mon objet Feature
                $game = $this->getOrCreateGame($feature->getIdGameApi());

                // Je vérifie si le Statut de mon objet Game est false et je le change en true 
                if (!$game->isStatus()) {
                    $game->setStatus(true);
                    // Je prépare mes données à être insérées dans mon objet Game
                    $em->persist($game);
                }

                // J'associe enfin mon objet Game à mon objet Feature
                $feature->setGame($game);
                // Je prépare mes données à être insérées dans mon objet Feature
                $em->persist($feature);
            }

            // Je flush mes opérations en attente (persist) vers ma base de données
            $em->flush();
            // J'envoie la transaction
            $em->commit();
        } catch (\Exception $e) {
            // En cas d'exception, j'annule toutes les opérations de la transaction
            $em->rollback();
            throw $e;
        }

        // Je redirige l'utilisateur vers la route 'app_moderator' après la validation des opérations
        return $this->redirectToRoute('app_moderator');
    }

    // Méthode privée pour obtenir ou créer un jeu basé sur l'ID de l'API du jeu
    private function getOrCreateGame($idGameApi): Game
    {
        // Je récupère l'entity manager
        $em = $this->entityManager;

        // Je cherche le jeu dans ma base de données en utilisant l'ID de l'API du jeu
        $game = $em->getRepository(Game::class)->findOneBy(['id_game_api' => $idGameApi]);

        $slugger = new AsciiSlugger();
        $slug = $slugger->slug($this->igdbApiService->getGameById($idGameApi)[0]["slug"]);

        // Si aucun jeu n'est trouvé, je crée un nouveau jeu
        if (!$game) {
            $game = new Game();
            $game->setIdGameApi($idGameApi);
            $game->setSlug($slug);
            $em->persist($game);
            $em->flush();
        }

        // Je retourne le jeu trouvé ou crée
        return $game;
    }

    // Méthode pour refuser une feature
    #[Route('/moderator/feature/{id}/{slug}/deny', name: 'deny_feature_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function denyFeature(?Feature $feature): Response
    {
        // Je récupère l'entity manager
        $em = $this->entityManager;

        // Je passe le State de mon objet Feature à DENIED, je persist et je flush les informations
        $feature->setState(FeatureState::DENIED);
        $em->persist($feature);
        $em->flush();

        // Ensuite je met à jour le Status du jeu associé si besoin
        $this->updateGameStatus($feature->getGame(), $em);

        // Je redirige l'utilisateur vers la route 'app_moderator' après la validation des opérations
        return $this->redirectToRoute('app_moderator');
    }

    // Méthode privée qui me permet de mettre à jour le statut du jeu de true à false si aucune feature d'accessibilité ne lui est attribué suite à des modifications parvenues, ou en cas de mauvaise manipulation.
    // Le jeu reste dans la base de données, si le jeu possède minimum une Feature en State "ACCEPTED", le jeu restera en Status "1", autrement il passe à 0 et n'est donc pas visible dans la gamelist.
    private function updateGameStatus(?Game $game): void
    {
        // Je récupère l'entity manager
        $em = $this->entityManager;

        // Si $game est null, la méthode se termine ici car il n'y a rien à mettre à jour
        if ($game === null) {
            return;
        }

        // Je filtre les Features acceptées du jeu
        $acceptedFeatures = $game->getFeatures()->filter(function (Feature $feature) {
            return $feature->getState() === FeatureState::PROCESSED;
        });

        // Si aucune fonctionnalité dans le State "ACCEPTED" n'est trouvée, le statut du jeu est mis à false, sinon à true
        if ($acceptedFeatures->isEmpty()) {
            $game->setStatus(false);
        } else {
            $game->setStatus(true);
        }

        // Je persist et je flush les informations
        $em->persist($game);
        $em->flush();
    }
    #endregion
}
