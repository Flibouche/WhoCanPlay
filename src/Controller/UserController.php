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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends AbstractController
{
    #region Settings
    // Méthode pour accéder au profil privé de l'utilisateur
    #[Route('/settings', name: 'app_user')]
    #[IsGranted('ROLE_USER')]
    public function settings(Security $security): Response
    {
        // Je récupère l'utilisateur connecté
        $user = $security->getUser();

        // Si l'utilisateur n'est pas connecté, alors je lève une exception
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw new AccessDeniedException('Access denied');
        }

        return $this->render('user/settings.html.twig', [
            'controller_name' => 'UserController'
        ]);
    }
    #endregion

    #region Account
    // Méthode pour accéder aux paramètres du compte de l'utilisateur
    #[Route('/account', name: 'app_user_account')]
    public function account(Security $security, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        // Je récupère l'utilisateur connecté
        $user = $security->getUser();

        // Si l'utilisateur n'est pas connecté, alors je lève une exception
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw new AccessDeniedException('Access denied');
        }

        // Je crée un formulaire pour modifier le mot de passe de l'utilisateur
        $form = $this->createForm(EditPasswordFormType::class, $user);
        $form->handleRequest($request);
        // Si le formulaire est soumis et valide, alors j'appelle la méthode updatePassword
        if ($form->isSubmitted() && $form->isValid()) {
            $response = $this->updatePassword($security, $form, $passwordHasher, $entityManager);

            // Si la méthode updatePassword retourne une instance de Response, alors je retourne cette instance
            if ($response instanceof Response) {
                return $response;
            }
        }

        return $this->render('user/account.html.twig', [
            'controller_name' => 'UserController',
            'formEditPassword' => $form,
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

    // Méthode pour supprimer le compte de l'utilisateur et d'anonymiser les données
    #[Route('/delete-account', name: 'app_user_delete_account')]
    #[IsGranted('ROLE_USER')]
    public function deleteAccount(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage): Response
    {
        /**
         * @var User|null $user
         */
        // Je récupère l'utilisateur actuellement connecté
        $user = $this->getUser();

        if (!$user instanceof UserInterface) {
            throw new AccessDeniedException('Access denied');
        }

        $topics = $user->getTopics();
        foreach ($topics as $topic) {
            $topic->setUser(null);
            $entityManager->persist($topic);
        }

        $posts = $user->getPosts();
        foreach ($posts as $post) {
            $post->setUser(null);
            $entityManager->persist($post);
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $tokenStorage->setToken(null);

        $this->addFlash('success', 'Account deleted successfully');

        return $this->redirectToRoute('app_home');
    }
    #endregion
    
    #region Features
    // Méthode pour afficher les fonctionnalités soumises par l'utilisateur
    #[Route('/submitted-features', name: 'app_user_submitted_features')]
    public function submittedFeatures(Security $security): Response
    {
        return $this->render('user/submittedFeatures.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
    #endregion

    #region Public Profile
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
    #endregion
}
