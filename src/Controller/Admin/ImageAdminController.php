<?php

namespace App\Controller\Admin;

use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/', name: 'show')]
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
}