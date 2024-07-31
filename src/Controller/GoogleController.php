<?php

namespace App\Controller;

use Exception;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class GoogleController extends AbstractController
{
    // Je vais sur le client Google de connexion grâce à cette méthode
    #[Route('/connect/google', name: 'connect_google')]
    public function connectAction(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect([], []);
    }

    // Je récupère les informations de l'utilisateur Google et je le connecte
    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(
        Request $request,
        ClientRegistry $clientRegistry,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
    ) {
        try {
            $client = $clientRegistry->getClient('google');
            /**
            * @var User|null $googleUser
            */
            $googleUser = $client->fetchUser();

            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $googleUser->getEmail()]);

            if (!$existingUser) {
                $user = new User();
                $user->setEmail($googleUser->getEmail());
                $user->setVerified(true);
                $user->setPassword(bin2hex(random_bytes(16)));
                $user->setGoogleUser(true);
                $user->setPseudo('Test');

                $entityManager->persist($user);
                $entityManager->flush();
            } else {
                $user = $existingUser;
            }

            $token = new UsernamePasswordToken($user, 'main', $user->getRoles());

            $event = new InteractiveLoginEvent($request, $token);
            $eventDispatcher->dispatch($event);

            return $this->redirectToRoute('app_home');

        } catch (\Exception $e) {
            return $this->redirectToRoute('app_login', ['error' => $e->getMessage()]);
        }
    }
}
