<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Image;
use App\Entity\Feature;
use App\Form\FeatureType;
use App\Service\ImageService;
use App\Service\IgdbApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FeatureController extends AbstractController
{

    private $igdbApiService;
    private $entityManager;
    private $imageService;

    public function __construct(IgdbApiService $igdbApiService, EntityManagerInterface $entityManager, ImageService $imageService)
    {
        $this->igdbApiService = $igdbApiService;
        $this->entityManager = $entityManager;
        $this->imageService = $imageService;
    }

    // Méthode pour envoyer une Feature à un jeu pour traitement
    #[Route('/feature', name: 'app_feature')]
    #[Route('/feature/{id}/{slug}/edit', name: 'edit_feature')]
    public function featureSubmission(?Feature $feature, Request $request): Response
    {
        // Je récupère l'entity manager et le service d'image
        $em = $this->entityManager;
        $is = $this->imageService;

        // Récupère le nom du jeu si je suis en mode édition
        $gameName = null;

        // Si la feature n'existe pas, on en crée une nouvelle
        if (!$feature) {
            $feature = new Feature();
        }
        
        // Si la feature existe, on récupère le nom du jeu
        if ($feature && $feature->getIdGameApi()) {
            $idGameApi = $feature->getIdGameApi();
            $gameName = $this->igdbApiService->getGameById($idGameApi);
        }
    
        // Je crée le formulaire
        $form = $this->createForm(FeatureType::class, $feature);
        $form->handleRequest($request);
    
        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // J'appelle la méthode privée pour envoyer la feature au jeu
            $this->sendFeatureToGame($feature, $form, $em, $is);
            return $this->redirectToRoute('app_home');
        }
    
        // J'affiche la vue avec le formulaire et les données
        return $this->render('feature/featureSubmission.html.twig', [
            'controller_name' => 'FeatureController',
            'formSendFeatureToGame' => $form,
            'edit' => $feature,
            'game' => $gameName,
        ]);
    }
    
    // Méthode privée pour envoyer une feature à un jeu pour traitement
    private function sendFeatureToGame(Feature $feature, FormInterface $form): void
    {
        // Je récupère l'entity manager, le service d'image et l'utilisateur
        $em = $this->entityManager;
        $is = $this->imageService;
        $user = $this->getUser();

        // Je récupère les images du formulaire et l'id du jeu de l'API
        $images = $form->get('images')->getData();
        $idGameApi = $form->get('id_game_api')->getData();
        $featureName = $form->get('name')->getData();
        $imagesDescription = $form->get('description')->getData();
    
        // Pour chaque image, je la traite et l'ajoute à la feature
        foreach ($images as $image) {
            $folder = 'features';
            $file = $is->add($image, $folder, 300, 300);
            $img = new Image();
            $img->setUrl($file);
            $img->setTitle($featureName);
            $img->setAltText($featureName);
            $img->setDescription($imagesDescription);
            $feature->addImage($img);
        }
    
        // Je cherche si le jeu existe déjà dans la base de données en fonction de l'id de l'API
        $game = $em->getRepository(Game::class)->findOneBy(['id_game_api' => $idGameApi]);
        // Si le jeu n'existe pas, j'ajoute l'id de l'API à la feature
        if ($game) {
            $feature->setGame($game);
        }
    
        // J'ajoute l'utilisateur et l'id de l'API à la feature
        $feature->setUser($user);
        $feature->setIdGameApi($idGameApi);
        
        // Je persiste et flush la feature
        $em->persist($feature);
        $em->flush();
    }

    // Méthode pour supprimer une image d'une feature
    #[Route('/delete/image/{id}', name: 'delete_image_feature', methods: ['DELETE'])]
    public function deleteImage(Image $image, Request $request): JsonResponse
    {
        // Je récupère l'entity manager et le service d'image
        $em = $this->entityManager;
        $is = $this->imageService;

        // Je récupère le contenu de la requête et le décode
        $data = json_decode($request->getContent(), true);

        // Si le token csrf est valide
        if($this->isCsrfTokenValid('delete'.$image->getId(), $data['_token'])) {
            // Je récupère l'URL de l'image
            $url = $image->getUrl();

            // Si la suppression de l'image est un succès
            if($is->delete($url, 'features', 300, 300)) {
                // Je supprime l'image de la base de données
                $em->remove($image);
                $em->flush();

                // Je retourne une réponse JSON avec un message de succès
                return new JsonResponse(['success' => true], 200);
            }
            // Sinon, je retourne une réponse JSON avec un message d'erreur
            return new JsonResponse(['error' => 'Delete failed'], 400);
        }
        // Si le token csrf n'est pas valide, je retourne une réponse JSON avec un message d'erreur
        return new JsonResponse(['error' => 'Invalid token'], 400);
    }

    // Méthode pour rechercher un jeu dans l'API IGDB
    #[Route('/search', name: 'search_api_game')]
    public function search(Request $request): JsonResponse
    {
        // Je récupère le nom du jeu depuis la requête
        $name = $request->query->get('game');

        // Je fais appel au service pour récupérer les jeux basés sur le nom
        $games = $this->igdbApiService->getGames($name);

        // Je formate les jeux pour les afficher dans la vue
        $formattedGames = array_map(function($game) {
            return [
                'id' => $game['id'],
                'name' => $game['name'],
                'cover' => $game['cover'],
            ];
        }, $games);

        // Je retourne une réponse JSON avec les jeux formatés
        return new JsonResponse($formattedGames);
    }
}