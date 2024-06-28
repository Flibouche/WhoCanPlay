<?php

namespace App\Controller;

use App\Entity\Subtype;
use App\Form\SubtypeType;
use App\Enum\SubtypeState;
use App\Service\IgdbApiService;
use App\Repository\SubtypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use DOMDocument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ModeratorController extends AbstractController
{

    private $igdbApiService;

    public function __construct(IgdbApiService $igdbApiService)
    {
        $this->igdbApiService = $igdbApiService;
    }

    #[Route('/moderator', name: 'app_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function submissionsBox(SubtypeRepository $subtypeRepository): Response
    {
        // Je définis les états que je veux récupérer
        $states = [SubtypeState::NOT_OPENED, SubtypeState::PENDING, SubtypeState::ACCEPTED, SubtypeState::DENIED];

        // J'utilise la méthode findSubtypesByStates pour récupérer les Subtypes par état
        $subtypes = $subtypeRepository->findSubtypesByStates($states);

        // J'initialise un tableau vide pour stocker les Subtypes avec les jeux associés
        $subtypesGames = [
            'notOpened' => [],
            'pending' => [],
            'processed' => [],
            'denied' => [],
        ];

        // Je fais une boucle foreach pour chaque Subtype récupéré
        foreach ($subtypes as $subtype) {
            // Je récupère l'ID idGameApi à partir de $subtype
            $idGameApi = $subtype->getIdGameApi();

            // J'utilise le service igdbApiService pour obtenir les informations du jeu en utilisant $idGameApi
            $gameApi = $this->igdbApiService->getGameById($idGameApi);

            // Je détermine l'état du Subtype pour le classer correctement
            // Utilisation de match plutôt que de switch pour rendre le code plus lisible
            $stateKey = match ($subtype->getState()) {
                SubtypeState::NOT_OPENED => 'notOpened',
                SubtypeState::PENDING => 'pending',
                SubtypeState::ACCEPTED => 'processed',
                SubtypeState::DENIED => 'denied',
                default => null,
            };

            // Vérification si $stateKey est différent de null
            if ($stateKey !== null) {
                // J'ajoute le Subtype et les informations du jeu associé dans le tableau
                $subtypesGames[$stateKey][] = [
                    'subtype' => $subtype,
                    'gameApi' => $gameApi ? $gameApi[0] : null, // On utilise le premier élément de $gameApi ou null s'il est vide
                ];
            } // TODO : gérer le cas où $stateKey est null
        }

        // Je retourne la vue Twig et je passe le tableau $subtypesGames à la vue
        return $this->render('moderator/index.html.twig', [
            'subtypes' => $subtypesGames,
        ]);
    }

    #[Route('/moderator/subtype/{id}/{slug}', name: 'show_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function showSubtype(Subtype $subtype, EntityManagerInterface $entityManager): Response
    {

        // Dès que j'accède à un Subtype, si son state est "Not opened", il passe automatiquement en "Pending"
        if ($subtype->getState() == SubtypeState::NOT_OPENED) {
            $subtype->setState(SubtypeState::PENDING);
            $entityManager->persist($subtype);
            $entityManager->flush();
        }

        return $this->render('moderator/show.html.twig', [
            'subtype' => $subtype,
        ]);
    }

    #[Route('/moderator/subtype/{id}/{slug}/edit', name: 'edit_subtype_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function editSubtype(Subtype $subtype = null, EntityManagerInterface $entityManager, Request $request): Response
    {

        $form = $this->createForm(SubtypeType::class, $subtype);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $subtype = $form->getData();

            $entityManager->persist($subtype);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('subtype/add.html.twig', [
            'formSendSubtypeToGame' => $form,
            'edit' => $subtype->getId(),
        ]);
    }

    #[Route('/moderator/subtype/{id}/{slug}/validate', name: 'validate_subtype_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function validateSubtype(Subtype $subtype = null, EntityManagerInterface $entityManager): Response
    {
        $subtype->setState(SubtypeState::ACCEPTED);

        $entityManager->persist($subtype);
        $entityManager->flush();

        return $this->redirectToRoute('app_moderator');
    }

    #[Route('/moderator/subtype/{id}/{slug}/deny', name: 'deny_subtype_moderator')]
    #[IsGranted('ROLE_MODERATOR')]
    public function denySubtype(Subtype $subtype = null, EntityManagerInterface $entityManager): Response
    {
        $subtype->setState(SubtypeState::DENIED);

        $entityManager->persist($subtype);
        $entityManager->flush();

        return $this->redirectToRoute('app_moderator');
    }
}