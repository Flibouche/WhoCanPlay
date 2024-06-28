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
    public function index(): Response
    {
        return $this->render('feature/index.html.twig', [
            'controller_name' => 'FeatureController',
        ]);
    }

    // Ajax method
    // #[Route('/search', name: 'search_api_game')]
    // public function search(Request $request): JsonResponse
    // {
    //     // Récupère la clé de recherche 'game' depuis la requête
    //     $name = $request->query->get('game');

    //     // Appel du service pour récupérer les jeux basés sur le nom
    //     $games = $this->igdbApiService->getGames($name);

    //     return new JsonResponse($games);

    //     // Rendu du template 'api/search.html.twig' avec les données des jeux
    //     // return $this->render('feature/search.html.twig', [
    //     //     'games' => $games,
    //     // ]);
    // }

    #[Route('/search/{name}', name: 'search_api_game')]
    public function search(Request $request, $name = null): Response
    {
        // Récupère la clé de recherche 'game' depuis la requête
        $name = $request->query->get('game');

        // Appel du service pour récupérer les jeux basés sur le nom
        $games = $this->igdbApiService->getGames($name);

        // Rendu du template 'api/search.html.twig' avec les données des jeux
        return $this->render('feature/search.html.twig', [
            'games' => $games,
        ]);
    }

    #[Route('/feature/add', name: 'add_feature')]
    public function sendFeatureToTreatment(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupération de l'ID du jeu depuis les paramètres de requête
        $idGameApi = $request->query->get('game');

        // Création d'une nouvelle instance de Feature
        $feature = new Feature();

        // Création du formulaire en utilisant FeatureType comme formulaire de type
        $form = $this->createForm(FeatureType::class);
        $form->handleRequest($request);

        // Vérification si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {

            // Recherche du jeu correspondant à l'ID du jeu API dans la base de données
            $game = $entityManager->getRepository(Game::class)->findOneBy(['id_game_api' => $idGameApi]);

            // Récupération des données soumises par le formulaire
            $feature = $form->getData();

            // Si le jeu exsite, on ajoute l'ID du jeu à la Feature
            if ($game) {
                $feature->setGame($game);
            }

            // Attribution de l'ID du jeu API au feature avant de le persister
            $feature->setIdGameApi($idGameApi);
            $entityManager->persist($feature);
            $entityManager->flush();

            // Redirection vers la page d'accueil après l'ajout du feature
            return $this->redirectToRoute('app_home');
        }

        // Si le formulaire n'a pas été soumis ou n'est pas valide, affichage du formulaire
        return $this->render('feature/add.html.twig', [
            'formSendFeatureToGame' => $form,
            'edit' => $feature->getId(),
        ]);
    }
}