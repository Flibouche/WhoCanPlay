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

        $idGameApi = $request->query->get('game');

        $subtype = new Subtype();

        $form = $this->createForm(SubtypeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $game = $entityManager->getRepository(Game::class)->findOneBy(['id_game_api' => $idGameApi]);
            if (!$game) {
                $game = new Game();
                $game->setIdGameApi($idGameApi);
                $entityManager->persist($game);
            }
            
            $subtype = $form->getData();
            $subtype->setIdGameApi($idGameApi);
            $entityManager->persist($subtype);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('subtype/form.html.twig', [
            'formAddSubtypeToGame' => $form
        ]);
    }

    // #[Route('/subtype/addForm', name: 'addForm_subtype', methods: ['GET', 'POST'])]
    // public function addSubtypeToGame(EntityManagerInterface $entityManager, Request $request): Response
    // {

    //     $idGameApi = $request->query->get('game');

    //     if (!$idGameApi) {
    //         throw $this->createNotFoundException('Game ID is required');
    //     }

    //     // Créer une nouvelle instance de l'entité Subtype
    //     $subtype = new Subtype();

    //     // Créer le formulaire en utilisant SubtypeType
    //     $form = $this->createForm(SubtypeType::class, $subtype);
    //     $form->handleRequest($request);
    //     dd($form);

    //     // Vérifier si le formulaire est soumis et valide
    //     if ($form->isSubmitted() && $form->isValid()) {

    //         // Vérifier si le jeu existe déjà ou créer une nouvelle instance
    //         $game = $entityManager->getRepository(Game::class)->findOneBy(['idGameApi' => $idGameApi]);

    //         if (!$game) {
    //             $game = new Game();
    //             $game->setIdGameApi($idGameApi);
    //             $entityManager->persist($game);
    //         }

    //         $subtype = $form->getData();
    //         $subtype->setIdGameApi($idGameApi);
    //         $entityManager->persist($subtype);

    //         $entityManager->flush();

    //         // Redirection vers une autre page après enregistrement
    //         return $this->redirectToRoute('app_home');
    //     }
    // }
}