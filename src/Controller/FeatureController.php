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

    public function __construct(IgdbApiService $igdbApiService)
    {
        $this->igdbApiService = $igdbApiService;
    }

    // Méthode pour envoyer une Feature à un jeu pour traitement
    #[Route('/feature', name: 'app_feature')]
    #[Route('/feature/{id}/{slug}/edit', name: 'edit_feature')]
    public function index(?Feature $feature, Request $request, EntityManagerInterface $entityManager, ImageService $imageService): Response
    {
        if (!$feature) {
            $feature = new Feature();
        }
    
        $gameName = null;
        if ($feature && $feature->getIdGameApi()) {
            $idGameApi = $feature->getIdGameApi();
            $gameName = $this->igdbApiService->getGameById($idGameApi);
        }
    
        $form = $this->createForm(FeatureType::class, $feature);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $this->processFeatureForm($feature, $form, $entityManager, $imageService);
            return $this->redirectToRoute('app_home');
        }
    
        return $this->render('feature/index.html.twig', [
            'controller_name' => 'FeatureController',
            'formSendFeatureToGame' => $form,
            'edit' => $feature,
            'game' => $gameName,
        ]);
    }
    
    private function processFeatureForm(Feature $feature, FormInterface $form, EntityManagerInterface $entityManager, ImageService $imageService): void
    {
        $user = $this->getUser();
        $images = $form->get('images')->getData();
        $idGameApi = $form->get('id_game_api')->getData();
    
        foreach ($images as $image) {
            $folder = 'features';
            $file = $imageService->add($image, $folder, 300, 300);
            $img = new Image();
            $img->setUrl($file);
            $img->setTitle('Test');
            $img->setAltText('Test');
            $feature->addImage($img);
        }
    
        $game = $entityManager->getRepository(Game::class)->findOneBy(['id_game_api' => $idGameApi]);
        if ($game) {
            $feature->setGame($game);
        }
    
        $feature->setUser($user);
        $feature->setIdGameApi($idGameApi);
        
        $entityManager->persist($feature);
        $entityManager->flush();
    }

    #[Route('/delete/image/{id}', name: 'delete_image_feature', methods: ['DELETE'])]
    public function deleteImage(Image $image, Request $request, EntityManagerInterface $entityManager, ImageService $imageService): JsonResponse
    {
        // Je récupère le contenu de la requête
        $data = json_decode($request->getContent(), true);

        if($this->isCsrfTokenValid('delete'.$image->getId(), $data['_token'])) {
            // Le token csrf est valide
            // Je récupère l'URL de l'image
            $url = $image->getUrl();

            if($imageService->delete($url, 'features', 300, 300)) {
                // Je supprime l'image de la base de données
                $entityManager->remove($image);
                $entityManager->flush();

                return new JsonResponse(['success' => true], 200);
            }
            // La suppression a échoué
            return new JsonResponse(['error' => 'Delete failed'], 400);
        }

        return new JsonResponse(['error' => 'Invalid token'], 400);
    }

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
                'name' => $game['name'],
                'cover' => $game['cover'],
            ];
        }, $games);

        return new JsonResponse($formattedGames);
    }

    
}