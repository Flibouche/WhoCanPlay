<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin-{secret}/user', name: 'app_admin_user_')]
#[IsGranted('ROLE_ADMIN')]
class UserAdminController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Méthode pour afficher la liste des utilisateurs
    #[Route('/', name: 'show')]
    #[IsGranted('ROLE_ADMIN')]
    public function showUsers(string $secret, UserRepository $userRepository): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
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
    public function detailsUser(string $secret, User $user): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
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
    public function deleteUser(string $secret, User $user): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_user_show', ['secret' => $secret]);
    }
}