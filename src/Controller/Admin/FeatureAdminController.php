<?php

namespace App\Controller\Admin;

use App\Repository\FeatureRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/', name: 'show')]
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

    public function createFeature(string $secret): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->render('admin/features/create.html.twig', [
            'controller_name' => 'FeatureAdminController',
        ]);
    }

    public function updateFeature(string $secret): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->redirectToRoute('app_admin_feature_show', ['secret' => $secret]);
    }

    public function deleteFeature(string $secret): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->redirectToRoute('app_admin_feature_show', ['secret' => $secret]);
    }
}