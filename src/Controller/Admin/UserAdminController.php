<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin-{secret}/user', name: 'app_admin_user_')]
#[IsGranted('ROLE_ADMIN')]
class UserAdminController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager, private string $admin_secret)
    {
    }

    // Méthode pour afficher la liste des utilisateurs
    #[Route('/', name: 'show')]
    #[IsGranted('ROLE_ADMIN')]
    public function showUsers(UserRepository $userRepository): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        $users = $userRepository->findBy([], ['id' => 'DESC']);

        return $this->render('admin/users/show.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'users' => $users,
        ]);
    }

    // Méthode pour afficher les détails d'un utilisateur
    #[Route('/details/{id}', name: 'details')]
    #[IsGranted('ROLE_ADMIN')]
    public function detailsUser(User $user): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->render('admin/users/details.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'user' => $user,
        ]);
    }

    // Méthode pour supprimer un utilisateur
    #[Route('/delete/{id}', name: 'delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(User $user, CsrfTokenManagerInterface $csrfTokenManager, Request $request): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $token = new CsrfToken('delete_item', $request->request->get('_token'));

        if (!$csrfTokenManager->isTokenValid($token)) {
            throw $this->createAccessDeniedException('Token not valid');
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_user_show', ['secret' => $this->admin_secret]);
    }

    // Méthode pour bannir un utilisateur
    #[Route('/ban/{id}', name: 'ban')]
    #[IsGranted('ROLE_ADMIN')]
    public function banUser(User $user, CsrfTokenManagerInterface $csrfTokenManager, Request $request): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $token = new CsrfToken('ban_item', $request->request->get('_token'));

        if (!$csrfTokenManager->isTokenValid($token)) {
            throw $this->createAccessDeniedException('Token not valid');
        }

        $user->setBanned(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->addFlash('success', 'User has been banned');

        return $this->redirectToRoute('app_admin_user_show', ['secret' => $this->admin_secret]);
    }

    // Méthode pour débannir un utilisateur
    #[Route('/unban/{id}', name: 'unban')]
    #[IsGranted('ROLE_ADMIN')]
    public function unbanUser(User $user, CsrfTokenManagerInterface $csrfTokenManager, Request $request): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($this->admin_secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $token = new CsrfToken('unban_item', $request->request->get('_token'));

        if (!$csrfTokenManager->isTokenValid($token)) {
            throw $this->createAccessDeniedException('Token not valid');
        }

        $user->setBanned(false);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->addFlash('success', 'User has been unbanned');

        return $this->redirectToRoute('app_admin_user_show', ['secret' => $this->admin_secret]);
    }
}
