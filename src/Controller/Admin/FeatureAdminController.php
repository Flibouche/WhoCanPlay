<?php

namespace App\Controller\Admin;

use App\Entity\Feature;
use App\Repository\FeatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin-{secret}/feature', name: 'app_admin_feature_')]
#[IsGranted('ROLE_ADMIN')]
class FeatureAdminController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private string $admin_secret)
    {
    }

    // Méthode pour afficher toutes les fonctionnalités
    #[Route('/', name: 'show')]
    #[IsGranted('ROLE_ADMIN')]
    public function showFeatures(FeatureRepository $featureRepository): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
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
    public function detailsFeature(Feature $feature): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
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
    public function deleteFeature(Feature $feature, CsrfTokenManagerInterface $csrfTokenManager, Request $request): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$feature) {
            throw $this->createNotFoundException('Feature not found');
        }

        $token = new CsrfToken('delete_item', $request->request->get('_token'));

        if (!$csrfTokenManager->isTokenValid($token)) {
            throw $this->createAccessDeniedException('Token not valid');
        }

        $this->entityManager->remove($feature);
        $this->entityManager->flush();

        $this->addFlash('success', 'Feature deleted successfully');
        return $this->redirectToRoute('app_admin_feature_show', ['secret' => $this->admin_secret]);
    }
}