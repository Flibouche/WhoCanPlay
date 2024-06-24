<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Subtype;
use App\Form\SubtypeType;
use App\Service\IgdbApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SubtypeController extends AbstractController
{

    private $igdbApiService;

    public function __construct(IgdbApiService $igdbApiService)
    {
        $this->igdbApiService = $igdbApiService;
    }

    #[Route('/subtype', name: 'app_subtype')]
    public function index(): Response
    {
        return $this->render('subtype/index.html.twig', [
            'controller_name' => 'SubtypeController',
        ]);
    }

    #[Route('/search/{name}', name: 'search_api_game')]
    public function search(Request $request, $name = null): Response
    {
        // Récupère la clé de recherche 'game' depuis la requête
        $name = $request->query->get('game');

        // Appel du service pour récupérer les jeux basés sur le nom
        $games = $this->igdbApiService->getGames($name);

        // Rendu du template 'api/search.html.twig' avec les données des jeux
        return $this->render('subtype/search.html.twig', [
            'games' => $games,
        ]);
    }

    #[Route('/subtype/add', name: 'add_subtype')]
    public function addSubtypeToGame(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupération de l'ID du jeu depuis les paramètres de requête
        $idGameApi = $request->query->get('game');

        // Création d'une nouvelle instance de Subtype
        $subtype = new Subtype();

        // Création du formulaire en utilisant SubtypeType comme formulaire de type
        $form = $this->createForm(SubtypeType::class);
        $form->handleRequest($request);

        // Vérification si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()) {

            // Recherche du jeu correspondant à l'ID du jeu API dans la base de données
            $game = $entityManager->getRepository(Game::class)->findOneBy(['id_game_api' => $idGameApi]);

            // Si le jeu n'existe pas, création d'une nouvelle instance de Game
            if (!$game) {
                $game = new Game();
                $game->setIdGameApi($idGameApi);
                $entityManager->persist($game);
            }
            
            // Récupération des données soumises par le formulaire
            $subtype = $form->getData();

            // Attribution de l'ID du jeu API au subtype avant de le persister
            $subtype->setIdGameApi($idGameApi);
            $entityManager->persist($subtype);
            $entityManager->flush();

            // Redirection vers la page d'accueil après l'ajout du subtype
            return $this->redirectToRoute('app_home');
        }

        // Si le formulaire n'a pas été soumis ou n'est pas valide, affichage du formulaire
        return $this->render('subtype/form.html.twig', [
            'formAddSubtypeToGame' => $form
        ]);
    }
}