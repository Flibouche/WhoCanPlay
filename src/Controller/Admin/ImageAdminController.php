<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/admin-{secret}/image', name: 'app_admin_image_')]
#[IsGranted('ROLE_ADMIN')]
class ImageAdminController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private string $admin_secret)
    {
    }

    // Méthode pour afficher toutes les images
    #[Route('/', name: 'show')]
    #[IsGranted('ROLE_ADMIN')]
    public function showImages(ImageRepository $imageRepository): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
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
    public function detailsImage(Image $image): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->render('admin/images/details.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'image' => $image,
        ]);
    }

    // Méthode pour supprimer une image
    #[Route('/delete/{id}', name: 'delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteImage(Image $image, CsrfTokenManagerInterface $csrfTokenManager, Request $request): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$image) {
            throw $this->createNotFoundException('No image found');
        }

        $token = new CsrfToken('delete_item', $request->request->get('_token'));

        if (!$csrfTokenManager->isTokenValid($token)) {
            throw $this->createAccessDeniedException('Token not valid');
        }

        $this->entityManager->remove($image);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_image_show', ['secret' => $this->admin_secret]);
    }
}