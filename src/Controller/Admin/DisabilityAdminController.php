<?php

namespace App\Controller\Admin;

use App\Repository\DisabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin-{secret}/disability', name: 'app_admin_disability_')]
#[IsGranted('ROLE_ADMIN')]
class DisabilityAdminController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'show')]
    public function showDisabilities(string $secret, DisabilityRepository $disabilityRepository): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        $disabilities = $disabilityRepository->findBy([], ['id' => 'DESC']);

        return $this->render('admin/disabilities/show.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'disabilities' => $disabilities,
        ]);
    }

    public function deleteDisability(string $secret): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->redirectToRoute('app_admin_disability_show', ['secret' => $secret]);
    }
}