<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin-{secret}/post', name: 'app_admin_post_')]
#[IsGranted('ROLE_ADMIN')]
class PostAdminController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private string $admin_secret)
    {
    }

    // Méthode pour afficher tous les commentaires
    #[Route('/', name: 'show')]
    #[IsGranted('ROLE_ADMIN')]
    public function showPosts(PostRepository $postRepository): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
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
    public function detailsPost(Post $post): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->render('admin/posts/details.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'post' => $post,
        ]);
    }

    // Méthode pour modifier un commentaire
    #[Route('/edit/{id}', name: 'edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function editPost(?Post $post, Request $request): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($post);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_post_show', ['secret' => $this->admin_secret]);
        }

        return $this->render('admin/posts/edit.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'formEditPost' => $form->createView(),
            'post' => $post,
        ]);
    }

    // Méthode pour supprimer un commentaire
    #[Route('/delete/{id}', name: 'delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deletePost(Post $post, CsrfTokenManagerInterface $csrfTokenManager, Request $request): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $token = new CsrfToken('delete_item', $request->request->get('_token'));

        if (!$csrfTokenManager->isTokenValid($token)) {
            throw $this->createAccessDeniedException('Token not valid');
        }

        $this->entityManager->remove($post);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_post_show', ['secret' => $this->admin_secret]);
    }
}
