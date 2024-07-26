<?php

namespace App\Controller\Admin;

use App\Entity\Disability;
use App\Form\DisabilityType;
use App\Repository\DisabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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

    // Méthode pour afficher la liste des handicaps
    #[Route('/', name: 'show')]
    #[IsGranted('ROLE_ADMIN')]
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

    // Méthode pour afficher les détails d'un handicap
    #[Route('/details/{id}', name: 'details')]
    #[IsGranted('ROLE_ADMIN')]
    public function detailsDisability(string $secret, Disability $disability): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->render('admin/disabilities/details.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'disability' => $disability,
        ]);
    }

    // Méthode pour créer ou modifier un handicap
    #[Route('/create', name: 'create')]
    #[Route('/edit/{id}', name: 'edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function createOrEditDisability(string $secret, ?Disability $disability, Request $request): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$disability) {
            $disability = new Disability();
        }

        $form = $this->createForm(DisabilityType::class, $disability);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($disability);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_disability_show', ['secret' => $secret]);
        }

        return $this->render('admin/disabilities/create.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'formAddDisability' => $form,
            'disability' => $disability,
            'edit' => $disability->getId(),
        ]);
    }

    // Méthode pour supprimer un handicap
    #[Route('/delete/{id}', name: 'delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteDisability(string $secret, Disability $disability): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$disability) {
            throw $this->createNotFoundException('Disability not found');
        }

        $this->entityManager->remove($disability);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_disability_show', ['secret' => $secret]);
    }
}