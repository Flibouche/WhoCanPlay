<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin-{secret}/image', name: 'app_admin_image_')]
#[IsGranted('ROLE_ADMIN')]
class ImageAdminController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Méthode pour afficher toutes les images
    #[Route('/', name: 'show')]
    #[IsGranted('ROLE_ADMIN')]
    public function showImages(string $secret, ImageRepository $imageRepository): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        $images = $imageRepository->findBy([], ['id' => 'DESC']);

        return $this->render('admin/images/show.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'images' => $images,
        ]);
    }

    // Méthode pour afficher les détails d'une image
    #[Route('/details/{id}', name: 'details')]
    #[IsGranted('ROLE_ADMIN')]
    public function detailsImage(string $secret, Image $image): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->render('admin/images/details.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'image' => $image,
        ]);
    }

    // Méthode pour créer ou modifier une image
    #[Route('/create', name: 'create')]
    #[Route('/edit/{id}', name: 'edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function createOrEditImage(string $secret, ?Image $image, Request $request): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$image) {
            $image = new Image();
        }

        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($image);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_image_show', ['secret' => $secret]);
        }

        return $this->render('admin/images/create.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'formAddImage' => $form,
            'image' => $image,
            'edit' => $image->getId(),
        ]);
    }

    // Méthode pour supprimer une image
    #[Route('/delete/{id}', name: 'delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteImage(string $secret, Image $image): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$image) {
            throw $this->createNotFoundException('No image found');
        }

        $this->entityManager->remove($image);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_image_show', ['secret' => $secret]);
    }
}