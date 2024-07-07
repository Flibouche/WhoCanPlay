<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Post;
use App\Entity\Topic;
use App\Form\PostType;
use App\Form\TopicType;
use App\Service\IgdbApiService;
use App\Repository\GameRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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
        // Je cherche directement les jeux ayant le status 1, soit les jeux actifs
        $activeGames = $gameRepository->findBy(['status' => 1]);

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

        // Je retourne à la vue twig en y passant mon tableau $gamesApiInfo
        return $this->render('game/index.html.twig', [
            'games' => $gamesApiInfo,
        ]);
    }

    #[Route('/game/{id}/{slug}', name: 'show_game')]
    public function showGame(?Game $game, GameRepository $gameRepository): Response
    {

        $gameId = $game->getId();
        $processedFeatures = $gameRepository->findProcessedFeaturesByGame($gameId);

        $featuresByDisability = [];

        foreach ($processedFeatures as $feature) {
            $disability = $feature['disabilityName'];

            if (!isset($featuresByDisability[$disability])) {
                $featuresByDisability[$disability] = [];
            }
            $featuresByDisability[$disability][] = $feature;
        }

        $idGameApi = $game->getIdGameApi();

        $gameApi = $this->igdbApiService->getGameDetails($idGameApi);

        return $this->render('game/show.html.twig', [
            'game' => $game,
            'gameApi' => $gameApi,
            'featuresByDisability' => $featuresByDisability,
        ]);
    }

    #[Route('/forum/{id}/{slug}', name: 'forum_game')]
    public function showforum(?Game $game, Request $request, EntityManagerInterface $entityManager): Response
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
                $post->setContent($postData->getContent());
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
}
