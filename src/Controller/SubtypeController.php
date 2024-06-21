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

    #[Route('/subtype', name: 'add_subtype', methods: ['GET', 'POST'])]
    public function addSubtypeToGame(EntityManagerInterface $entityManager, Subtype $subtype = null, Request $request, Game $game = null): Response
    {

        // Créer une nouvelle instance de l'entité Subtype
        $subtype = new Subtype();

        // Créer le formulaire en utilisant SubtypeType
        $form = $this->createForm(SubtypeType::class, $subtype);

        // Gérer la soumission du formulaire
        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer l'ID du jeu sélectionné depuis la requête GET
            $idGameApi = $request->query->get('game');

            // Assigner l'ID récupéré au champ id_game_api de l'entité Subtype
            $subtype->setIdGameApi($idGameApi);

            // Redirection vers une autre page après enregistrement
            return $this->redirectToRoute('add_subtype');

        }

        return $this->render('subtype/form.html.twig', [
            'formAddSubtypeToGame' => $form,
        ]);
    }
}
