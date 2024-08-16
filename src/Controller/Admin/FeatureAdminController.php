<?php

namespace App\Controller\Admin;

use App\Entity\Feature;
use App\Form\FeatureType;
use App\Repository\FeatureRepository;
use App\Service\IgdbApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin-{secret}/feature', name: 'app_admin_feature_')]
#[IsGranted('ROLE_ADMIN')]
class FeatureAdminController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Méthode pour afficher toutes les fonctionnalités
    #[Route('/', name: 'show')]
    #[IsGranted('ROLE_ADMIN')]
    public function showFeatures(string $secret, FeatureRepository $featureRepository): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        $features = $featureRepository->findBy([], ['id' => 'DESC']);

        return $this->render('admin/features/show.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'features' => $features,
        ]);
    }

    // Méthode pour afficher les détails d'une fonctionnalité
    #[Route('/details/{id}', name: 'details')]
    #[IsGranted('ROLE_ADMIN')]
    public function detailsFeature(string $secret, Feature $feature): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->render('admin/features/details.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'feature' => $feature,
        ]);
    }

    // Méthode pour supprimer une fonctionnalité
    #[Route('/delete/{id}', name: 'delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteFeature(string $secret, Feature $feature): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$feature) {
            throw $this->createNotFoundException('Feature not found');
        }

        $this->entityManager->remove($feature);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_feature_show', ['secret' => $secret]);
    }
}