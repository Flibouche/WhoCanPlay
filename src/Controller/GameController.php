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
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GameController extends AbstractController
{

    private $igdbApiService;
    private $htmlSanitizer;

    public function __construct(IgdbApiService $igdbApiService, HtmlSanitizerInterface $htmlSanitizer)
    {
        $this->igdbApiService = $igdbApiService;
        $this->htmlSanitizer = $htmlSanitizer;
    }

    // Méthode pour afficher la liste des jeux
    #[Route('/game', name: 'app_game')]
    public function index(GameRepository $gameRepository): Response
    {
        // Je cherche directement les jeux ayant le status 1, soit les jeux actifs
        $activeGames = $gameRepository->findBy(['status' => 1]);

        // Je crée un tableau vide pour y stocker les IdGameApi
        $gameApiIds = [];
        foreach ($activeGames as $game) {
            $gameApiIds[] = $game->getIdGameApi();
        }

        // Avec mon nouveau tableau, je récupère les informations via le service de l'API en y passant mon tableau en argument
        $gamesApiData = $this->igdbApiService->getGameAndDetailsByIds($gameApiIds);

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

                // J'ajoute le traitement qui vas éviter d'afficher les Disabilities en double
                $uniqueDisabilities = [];
                foreach ($gameDb->getFeatures() as $feature) {
                    $disability = $feature->getDisability();
                    if (!isset($uniqueDisabilities[$disability->getId()])) {
                        $uniqueDisabilities[$disability->getId()] = $disability;
                    }
                }

                // J'ajoute un tableau associatif à mon tableau $gamesApiInfo qui contient maintenant les informations combinées des jeux de ma BDD et des jeux de l'API
                $gamesApiInfo[] = [
                    'db' => $gameDb,
                    'api' => $gameApi,
                    'uniqueDisabilities' => array_values($uniqueDisabilities),
                ];
            }
        } else {
            // Si aucun jeu actif n'est trouvé, j'ajoute un message flash "No game found !"
            $this->addFlash('warning', "No game found !");
        }

        // Je retourne à la vue twig en y passant mon tableau $gamesApiInfo
        return $this->render('game/index.html.twig', [
            'games' => $gamesApiInfo,
        ]);
    }

    // Méthode pour afficher les détails d'un jeu
    #[Route('/game/{id}/{slug}', name: 'show_game')]
    public function showGame(?Game $game, GameRepository $gameRepository): Response
    {
        if (!$game) {
            throw $this->createNotFoundException('The game does not exist');
        }

        $gameId = $game->getId();
        $processedFeatures = $gameRepository->findProcessedFeaturesByGame($gameId);

        $featuresByDisability = $this->organizeFeaturesByDisability($processedFeatures);

        $idGameApi = $game->getIdGameApi();
        $gameApi = $this->igdbApiService->getGameDetails($idGameApi);

        return $this->render('game/show.html.twig', [
            'game' => $game,
            'gameApi' => $gameApi,
            'featuresByDisability' => $featuresByDisability,
        ]);
    }

    // Méthode pour organiser les fonctionnalités par handicap et éviter les doublons
    private function organizeFeaturesByDisability(array $processedFeatures): array
    {
        // Je crée un tableau vide pour stocker les fonctionnalités organisées par handicap
        $features = [];

        // Je parcours les fonctionnalités traitées
        foreach ($processedFeatures as $result) {
            $featureName = $result['featureName'];
            $disabilityName = $result['disabilityName'];

            // Si la fonctionnalité n'existe pas encore dans le tableau, je l'ajoute avec ses informations
            if (!isset($features[$featureName])) {
                $features[$featureName] = [
                    'name' => $featureName,
                    'state' => $result['state'],
                    'content' => $result['content'],
                    'disabilityName' => $disabilityName,
                    'icon' => $result['icon'],
                    'images' => []
                ];
            }

            // Si l'URL de l'image n'est pas vide, je l'ajoute au tableau d'images de la fonctionnalité
            if (!empty($result['url'])) {
                $features[$featureName]['images'][] = [
                    'url' => $result['url'],
                    'altText' => $result['altText'],
                    'title' => $result['title'],
                    'description' => $result['description'],
                    'submissionDate' => $result['submissionDate']
                ];
            }
        }

        // Je crée un tableau vide pour stocker les fonctionnalités organisées par handicap
        $featuresByDisability = [];

        // Je parcours les fonctionnalités
        foreach ($features as $feature) {
            $disability = $feature['disabilityName'];

            // Si le handicap n'existe pas encore dans le tableau, je l'ajoute avec un tableau vide
            if (!isset($featuresByDisability[$disability])) {
                $featuresByDisability[$disability] = [];
            }
            // J'ajoute la fonctionnalité au tableau correspondant au handicap
            $featuresByDisability[$disability][] = $feature;
        }

        // Je retourne le tableau des fonctionnalités organisées par handicap
        return $featuresByDisability;
    }

    // Méthode pour afficher le forum d'un jeu
    #[Route('/forum/{id}/{slug}', name: 'forum_game')]
    public function showForum(?Game $game, Request $request, EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();

        $topic = new Topic();
        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->beginTransaction();

                $topic = $form->getData();
                $topic->setGame($game);
                $topic->setUser($user);

                $postData = $form->get('post')->getData();
                $post = new Post();
                // Créer un service dédié pour le sanitizedContent ?
                $sanitizedContent = $this->htmlSanitizer->sanitize($postData->getContent());
                $post->setContent($sanitizedContent);
                $post->setUser($user);
                $post->setTopic($topic);

                $entityManager->persist($topic);
                $entityManager->persist($post);
                $entityManager->flush();

                $entityManager->commit();

                $this->addFlash('success', 'Topic successfully created !');
                return $this->redirectToRoute('topic_game', [
                    'id' => $topic->getId(),
                    'slug' => $topic->getSlug()
                ]);
            } catch (\Exception $e) {
                $entityManager->rollback();
                $this->addFlash('error', 'An error occurred !');
                throw $e;
            }
        }

        return $this->render('game/forum.html.twig', [
            'game' => $game,
            'formAddTopic' => $form,
            'controller_name' => 'HomeController',
        ]);
    }

    // Méthode pour afficher un topic
    #[Route('/topic/{id}/{slug}', name: 'topic_game')]
    public function showTopic(?Topic $topic, PostRepository $postRepository, Request $request, EntityManagerInterface $entityManager): Response
    {

        $user = $this->getUser();

        $posts = $postRepository->findBy(['topic' => $topic], ["publicationDate" => "ASC"]);

        $post = new Post();

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->beginTransaction();
                $post = $form->getData();

                $sanitizedContent = $this->htmlSanitizer->sanitize($post->getContent());
                $post->setContent($sanitizedContent);

                $post->setUser($user);
                $post->setTopic($topic);

                $entityManager->persist($post);
                $entityManager->flush();

                $entityManager->commit();

                $this->addFlash('success', 'Topic successfully created !');
                return $this->redirectToRoute('topic_game', [
                    'id' => $topic->getId(),
                    'slug' => $topic->getSlug()
                ]);
            } catch (\Exception $e) {
                $entityManager->rollback();
                $this->addFlash('error', 'An error occurred !');
                throw $e;
            }
        }

        return $this->render('game/topic.html.twig', [
            'topic' => $topic,
            'posts' => $posts,
            'formAddPost' => $form,
        ]);
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
}
