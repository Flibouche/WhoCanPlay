<?php

namespace App\Controller\Admin;

use App\Repository\FeatureRepository;
use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin-{secret}', name: 'app_admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(string $secret, FeatureRepository $featureRepository, GameRepository $gameRepository): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        $features = $featureRepository->findFeatures();

        $featuresByState = [];

        foreach ($features as $feature) {
            $state = $feature['state']->value;
            if (!isset($featuresByState[$state])) {
                $featuresByState[$state] = 0;
            }
            $featuresByState[$state]++;
        }
        
        $games = $gameRepository->findBy(['status' => 1], ['id' => 'ASC']);
        $featuresByGame = [];

        foreach ($games as $game) {
            $featuresByGame[$game->getId()] = $featureRepository->findBy(['game' => $game->getId()]);
        }
        
        return $this->render('admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
            'featuresByState' => $featuresByState,
            'featuresByGame' => $games,
        ]);
    }
}