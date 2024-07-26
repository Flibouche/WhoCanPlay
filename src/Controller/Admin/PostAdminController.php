<?php

namespace App\Controller\Admin;

use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin-{secret}/post', name: 'app_admin_post_')]
#[IsGranted('ROLE_ADMIN')]
class PostAdminController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'show')]
    public function showPosts(string $secret, PostRepository $postRepository): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        $posts = $postRepository->findBy([], ['id' => 'DESC']);

        return $this->render('admin/posts/show.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'posts' => $posts,
        ]);
    }
}