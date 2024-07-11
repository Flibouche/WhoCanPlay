<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Feature;
use App\Form\FeatureType;
use App\Service\IgdbApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FeatureController extends AbstractController
{

    private $igdbApiService;

    public function __construct(IgdbApiService $igdbApiService)
    {
        $this->igdbApiService = $igdbApiService;
    }

    #[Route('/feature', name: 'app_feature')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {

        $feature = new Feature();

        // Je récupère l'User qui envoie la Feature en traitement
        $user = $this->getUser();

        // Création du formulaire en utilisant FeatureType comme formulaire de type
        $form = $this->createForm(FeatureType::class, $feature);
        $form->handleRequest($request);
        
        // Vérification si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {

            $idGameApi = $form->get('id_game_api')->getData();

            // Recherche du jeu correspondant à l'ID du jeu API dans la base de données
            $game = $entityManager->getRepository(Game::class)->findOneBy(['id_game_api' => $idGameApi]);

            // Si le jeu existe, on ajoute l'ID du jeu à la Feature
            if ($game) {
                $feature->setGame($game);
            }

            // J'ajoute le bon utilisateur à l'objet Feature que je vais créer
            $feature->setUser($user);

            // Attribution de l'ID du jeu API au feature avant de le persister
            $feature->setIdGameApi($idGameApi);
            $entityManager->persist($feature);
            $entityManager->flush();

            // Redirection vers la page d'accueil après l'ajout du feature
            return $this->redirectToRoute('app_home');
        }
        
        return $this->render('feature/index.html.twig', [
            'controller_name' => 'FeatureController',
            'formSendFeatureToGame' => $form,
        ]);
    }

    // Ajax method
    #[Route('/search', name: 'search_api_game')]
    public function search(Request $request): JsonResponse
    {
        // Récupère la clé de recherche 'game' depuis la requête
        $name = $request->query->get('game');

        // Appel du service pour récupérer les jeux basés sur le nom
        $games = $this->igdbApiService->getGames($name);

        $formattedGames = array_map(function($game) {
            return [
                'id' => $game['id'],
                'name' => $game['name']
            ];
        }, $games);

        return new JsonResponse($formattedGames);
    }
}