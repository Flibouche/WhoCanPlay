<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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

    // Méthode pour afficher tous les commentaires
    #[Route('/', name: 'show')]
    #[IsGranted('ROLE_ADMIN')]
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

    // Méthode pour afficher les détails d'un commentaire
    #[Route('/details/{id}', name: 'details')]
    #[IsGranted('ROLE_ADMIN')]
    public function detailsPost(string $secret, Post $post): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->render('admin/posts/details.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'post' => $post,
        ]);
    }

    // Méthode pour créer ou modifier un commentaire
    #[Route('/create', name: 'create')]
    #[Route('/edit/{id}', name: 'edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function createOrEditPost(string $secret, ?Post $post, Request $request): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$post) {
            $post = new Post();
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($post);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_post_show', ['secret' => $secret]);
        }

        return $this->render('admin/posts/create.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'post' => $post,
        ]);
    }

    // Méthode pour supprimer un commentaire
    #[Route('/delete/{id}', name: 'delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deletePost(string $secret, Post $post): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_post_show', ['secret' => $secret]);
    }
}
