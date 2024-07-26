<?php

namespace App\Controller\Admin;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/', name: 'show')]
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

    public function deleteUser(string $secret): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->redirectToRoute('app_admin_user_show', ['secret' => $secret]);
    }
}