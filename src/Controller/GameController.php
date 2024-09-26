<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Post;
use App\Entity\Topic;
use App\Form\PostType;
use App\Entity\Feature;
use App\Form\TopicType;
use App\Service\IgdbApiService;
use App\Repository\GameRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GameController extends AbstractController
{

    private $igdbApiService;
    private $htmlSanitizer;
    private $entityManager;

    public function __construct(IgdbApiService $igdbApiService, HtmlSanitizerInterface $htmlSanitizer, EntityManagerInterface $entityManager)
    {
        $this->igdbApiService = $igdbApiService;
        $this->htmlSanitizer = $htmlSanitizer;
        $this->entityManager = $entityManager;
    }

    #region Gamelist
    // Méthode pour afficher la liste des jeux
    #[Route('/games', name: 'app_games')]
    public function gameList(GameRepository $gameRepository): Response
    {
        // Je récupère les jeux actifs
        $activeGames = $gameRepository->findBy(['status' => 1]);

        // Si aucun jeu n'est trouvé, j'ajoute un message flash et je retourne un tableau vide
        if (empty($activeGames)) {
            $this->addFlash('warning', "No game found !");
            $gamesApiInfo = [];
            // Sinon, je traite les données des jeux
        } else {
            $gamesApiInfo = $this->processGamesData($activeGames); // J'appelle la méthode privée pour traiter les données des jeux
        }

        // Je retourne à la vue twig en y passant les jeux
        return $this->render('game/gamesList.html.twig', [
            'games' => $gamesApiInfo,
        ]);
    }

    // Méthode privée pour traiter les données des jeux
    private function processGamesData(array $activeGames): array
    {
        // Récupération des IDs des jeux actifs ($gameApiIds) en utilisant array_map pour plus de lisibilité
        // array_map applique une fonction anonyme (fn($game)) à chaque élément du tableau $activeGames
        // La fonction anonyme prend chaque élément du tableau $activeGames (chaque jeu) et retourne l'ID du jeu API
        $gameApiIds = array_map(fn ($game) => $game->getIdGameApi(), $activeGames); // Exemple : [0 => 26226, 1 => 2132, 2 => 299]

        // Je récupère les données des jeux actifs en utilisant les IDs des jeux API
        $gamesApiData = $this->igdbApiService->getGamesAndDetailsByIds($gameApiIds);

        // J'utilise array_column pour créer un tableau associatif avec les données des jeux API en utilisant l'ID du jeu comme clé
        // array_column prend en paramètres le tableau $gamesApiData, la valeur à extraire (null pour tout l'élément) et la clé à utiliser comme index
        // Le fait d'utiliser 'null' me permet de conserver les données complètes et d'accéder à tous les détails d'un jeu en utilisant son ID
        $gamesApiDataById = array_column($gamesApiData, null, 'id'); // Exemple : [26226 => ['id' => 26266, 'name' => 'Celeste'...]]

        // Je crée un tableau vide pour stocker les informations des jeux
        $gamesApiInfo = [];

        // Je parcours les jeux actifs
        foreach ($activeGames as $gameDb) {
            // Je récupère l'ID du jeu API
            $idGameApi = $gameDb->getIdGameApi();
            // Je récupère les données du jeu API en utilisant l'ID du jeu, ou null si le jeu n'existe pas
            $gameApi = $gamesApiDataById[$idGameApi] ?? null;
            // Je crée un tableau vide pour stocker les handicaps uniques afin d'éviter d'afficher plusieurs fois le même handicap
            $uniqueDisabilities = [];

            // Je parcours les fonctionnalités du jeu
            foreach ($gameDb->getFeatures() as $feature) {
                // Je récupère le handicap de la fonctionnalité
                $disability = $feature->getDisability();
                // Si le handicap n'existe pas encore dans le tableau, je l'ajoute
                if (!isset($uniqueDisabilities[$disability->getId()])) {
                    // J'ajoute le handicap au tableau des handicaps uniques
                    $uniqueDisabilities[$disability->getId()] = $disability;
                }
            }

            // J'ajoute les informations du jeu, les données du jeu API et les handicaps uniques au tableau $gamesApiInfo
            $gamesApiInfo[] = [
                'db' => $gameDb,
                'api' => $gameApi,
                'uniqueDisabilities' => array_values($uniqueDisabilities),
            ];
        }

        // Je retourne le tableau des informations des jeux
        return $gamesApiInfo;
    }
    #endregion

    #region Game 
    // Méthode pour afficher les détails d'un jeu
    #[Route('/game/{id}/{slug}', name: 'show_game')]
    public function showGame(?Game $game, GameRepository $gameRepository): Response
    {
        // Je vérifie si le jeu existe
        if (!$game) {
            throw $this->createNotFoundException('The game does not exist');
        }

        // Je récupère l'ID du jeu
        $gameId = $game->getId();
        // Je récupère les détails des fonctionnalités traitées du jeu en faisant appel à la méthode du repository
        $processedFeatures = $gameRepository->findProcessedFeaturesByGame($gameId);

        if(count($processedFeatures) === 0) {
            // throw new NotFoundHttpException('No features found for this game');
            throw $this->createNotFoundException('No features found for this game');
        }

        // J'appelle la méthode privée pour organiser les fonctionnalités par handicap
        $featuresByDisability = $this->organizeFeaturesByDisability($processedFeatures);

        // Je récupère les données du jeu API
        $idGameApi = $game->getIdGameApi();
        // J'appelle le service pour récupérer les détails du jeu API
        $gameApi = $this->igdbApiService->getGameDetails($idGameApi);

        // Je retourne à la vue twig en y passant le jeu, les données du jeu API et les fonctionnalités organisées par handicap
        return $this->render('game/showGame.html.twig', [
            'game' => $game,
            'gameApi' => $gameApi,
            'featuresByDisability' => $featuresByDisability,
        ]);
    }

    // Méthode pour organiser les fonctionnalités par handicap et éviter les doublons
    private function organizeFeaturesByDisability(array $processedFeatures): array
    {
        // Je crée un tableau vide pour stocker les fonctionnalités
        $features = [];

        // Je parcours les fonctionnalités traitées
        foreach ($processedFeatures as $feature) {
            $featureId = $feature['id'];
            $featureName = $feature['featureName'];
            $disabilityName = $feature['disabilityName'];
            $pseudo = $feature['pseudo'];

            // Si la fonctionnalité n'existe pas encore dans le tableau, je l'ajoute avec ses informations
            if (!isset($features[$featureName])) {
                $features[$featureName] = [
                    'id' => $featureId,
                    'name' => $featureName,
                    'state' => $feature['state'],
                    'content' => $feature['content'],
                    'disabilityName' => $disabilityName,
                    'icon' => $feature['icon'],
                    'images' => [],
                    'user' => $pseudo
                ];
            }

            // Si l'URL de l'image n'est pas vide, je l'ajoute au tableau d'images de la fonctionnalité
            if (!empty($feature['url'])) {
                $features[$featureName]['images'][] = [
                    'url' => $feature['url'],
                    'altText' => $feature['altText'],
                    'title' => $feature['title'],
                    'submissionDate' => $feature['submissionDate']
                ];
            }
        }

        // Je crée un tableau pour organiser les fonctionnalités par handicap afin d'éviter les doublons
        $organizedByDisability = [];

        // Je parcours les fonctionnalités pour les organiser par handicap
        foreach ($features as $feature) {
            $disability = $feature['disabilityName'];

            // Si le handicap n'existe pas encore dans le tableau, je l'ajoute avec un tableau vide
            if (!isset($organizedByDisability[$disability])) {
                $organizedByDisability[$disability] = [];
            }

            // J'ajoute la fonctionnalité au tableau correspondant au handicap
            $organizedByDisability[$disability][] = $feature;
        }

        // Je retourne le tableau des fonctionnalités organisées par handicap
        return $organizedByDisability;
    }
    #endregion

    #region Forum
    // Méthode pour afficher le forum d'un jeu
    #[Route('/forum/{id}/{slug}', name: 'forum_game')]
    public function showForum(?Game $game, Request $request): Response
    {
        // Je vérifie si le jeu existe
        if (!$game) {
            throw $this->createNotFoundException('The game does not exist');
        }

        // Je crée un nouveau topic
        $topic = new Topic();
        // Je crée un formulaire pour ajouter un topic
        $form = $this->createForm(TopicType::class, $topic);
        // Je traite la requête
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $response = $this->addTopic($form, $game);

            // Si la méthode addTopic retourne une réponse, je la retourne
            if ($response instanceof Response) {
                return $response;
            }
        }

        // Je retourne à la vue twig en y passant le jeu et le formulaire
        return $this->render('game/showForum.html.twig', [
            'game' => $game,
            'formAddTopic' => $form,
            'controller_name' => 'HomeController',
        ]);
    }

    // Méthode privée pour ajouter un topic
    private function addTopic($form, $game): ?Response
    {
        // Je récupère l'entity manager et l'utilisateur connecté
        $em = $this->entityManager;
        $user = $this->getUser();

        // Je commence la transaction
        $em->beginTransaction();
        try {
            // Je récupère les données du formulaire
            $topic = $form->getData();
            // J'ajoute le jeu et l'utilisateur au topic
            $topic->setGame($game);
            $topic->setUser($user);

            // Je récupère le titre du topic et le contenu du post
            $topicTitle = $form->get('title')->getData();
            $postContent = $form->get('post')->getData();
            
            // Je nettoie le contenu du titre et du post
            $sanitizedTitle = $this->htmlSanitizer->sanitize($topicTitle);
            $sanitizedContent = $this->htmlSanitizer->sanitize($postContent->getContent());

            // J'ajoute le contenu nettoyé au titre du topic et au post et j'ajoute le user et le topic correspondant
            $topic->setTitle($sanitizedTitle);
            $post = new Post();
            $post->setContent($sanitizedContent);
            $post->setUser($user);
            $post->setTopic($topic);

            // Je persiste le topic et le post et je flush
            $em->persist($topic);
            $em->persist($post);
            $em->flush();

            // Je commit la transaction
            $em->commit();

            // J'ajoute un message flash de succès
            $this->addFlash('success', 'Topic successfully created !');
            
            // Je redirige vers le topic
            return $this->redirectToRoute('topic_game', [
                'id' => $topic->getId(),
                'slug' => $topic->getSlug()
            ]);
            // Si une exception est levée
        } catch (\Exception $e) {
            // Je rollback la transaction
            $em->rollback();
            $this->addFlash('error', 'An error occurred !');
            throw $e;
        }
    }
    #endregion

    #region Topic
    // Méthode pour afficher un topic
    #[Route('/topic/{id}/{slug}', name: 'topic_game')]
    public function showTopic(?Topic $topic, PostRepository $postRepository, Request $request): Response
    {
        // Je vérifie si le topic existe
        if (!$topic) {
            throw $this->createNotFoundException('The topic does not exist');
        }

        // Je récupère les posts du topic triés par date de publication
        $posts = $postRepository->findBy(['topic' => $topic], ["publicationDate" => "ASC"]);

        // Je crée un nouveau post
        $post = new Post();
        // Je crée un formulaire pour ajouter un post
        $form = $this->createForm(PostType::class, $post);
        // Je traite la requête
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $response = $this->addPost($form, $topic);

            // Si la méthode addPost retourne une réponse, je la retourne
            if ($response instanceof Response) {
                return $response;
            }
        }

        // Je retourne à la vue twig en y passant le topic, les posts et le formulaire
        return $this->render('game/showTopic.html.twig', [
            'topic' => $topic,
            'posts' => $posts,
            'formAddPost' => $form,
        ]);
    }

    // Méthode privée pour ajouter un post
    private function addPost($form, $topic): ?Response
    {
        // Je récupère l'entity manager et l'utilisateur connecté
        $em = $this->entityManager;
        $user = $this->getUser();
        
        // Je commence la transaction
        $em->beginTransaction();
        try {
            // Je récupère les données du formulaire
            $post = $form->getData();
            // Je nettoie le contenu du post
            $sanitizedContent = $this->htmlSanitizer->sanitize($post->getContent());
            // J'ajoute le contenu nettoyé au post et j'ajoute le user et le topic
            $post->setContent($sanitizedContent);
            $post->setUser($user);
            $post->setTopic($topic);

            // Je persiste le post et je flush
            $em->persist($post);
            $em->flush();

            // Je commit la transaction
            $em->commit();

            // J'ajoute un message flash de succès
            $this->addFlash('success', 'Post successfully created !');
            // Je redirige vers le topic
            return $this->redirectToRoute('topic_game', [
                'id' => $topic->getId(),
                'slug' => $topic->getSlug()
            ]);
            // Si une exception est levée
        } catch (\Exception $e) {
            // Je rollback la transaction
            $em->rollback();
            $this->addFlash('error', 'An error occurred !');
            throw $e;
        }
    }

    // Méthode pour éditer un post
    #[Route('/api/topic/{id}/edit', name: 'edit_post_game', methods: ["POST"])]
    public function editPost(?Post $post, int $id, Request $request, PostRepository $postRepository, EntityManagerInterface $entityManager)
    {
        $requestData = json_decode($request->getContent(), true);
        $post = $postRepository->findOneById($requestData['id']);

        $post->setContent($requestData['content']);
        $post->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));

        $entityManager->persist($post);
        $entityManager->flush();

        return $this->json(['success' => true, 'message' => "Post edité"], 200);
    }
    #endregion
}