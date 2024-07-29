<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditPasswordFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends AbstractController
{
    // Méthode pour accéder au profil privé de l'utilisateur
    #[Route('/settings', name: 'app_user')]
    #[IsGranted('ROLE_USER')]
    public function settings(Security $security, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        // Je récupère l'utilisateur connecté
        $user = $security->getUser();

        // Si l'utilisateur n'est pas connecté, alors je lève une exception
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw new AccessDeniedException('Access denied');
        }

        $form = $this->createForm(EditPasswordFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $response = $this->updatePassword($security, $form, $passwordHasher, $entityManager);
        }

        return $this->render('user/settings.html.twig', [
            'controller_name' => 'UserController',
            'formEditPassword' => $form,
        ]);
    }

    // Méthode pour afficher le profil public de l'utilisateur
    #[Route('/{pseudo}', name: 'app_user_profile')]
    #[IsGranted('ROLE_USER')]
    public function publicProfile(string $pseudo, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['pseudo' => $pseudo]);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        return $this->render('user/publicProfile.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user,
        ]);
    }

    // Méthode pour modifier le mot de passe de l'utilisateur
    private function updatePassword(Security $security, $form, $passwordHasher, $entityManager): Response
    {
        // Je récupère l'utilisateur connecté
        $user = $security->getUser();

        // Si l'utilisateur n'est pas connecté, alors je lève une exception
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw new AccessDeniedException('Access denied');
        }

        // Je récupère l'ancien mot de passe
        $oldPassword = $form->get('oldPassword')->getData();

        // Si le mot de passe actuel n'est pas valide, alors j'envoi un message d'erreur
        if (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
            $this->addFlash('error', 'Invalid current password');
        } else {
            // Je récupère le nouveau mot de passe
            $newPassword = $form->get('plainPassword')->getData();
            // J'encode le nouveau mot de passe
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            /**
             * @var User|null $user
             */
            // J'assigne le nouveau mot de passe à l'utilisateur
            $user->setPassword($hashedPassword);

            // Je persiste et je flush les données
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Password updated successfully');
        }

        return $this->redirectToRoute('app_user');
    }

    public function deleteAccount()
    {
        /**
         * @var User|null $user
         */
        
        // Je récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Access denied');
        }


        $posts = $user->getPosts();
    }
}
