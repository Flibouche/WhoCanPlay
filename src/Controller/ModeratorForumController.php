<?php

namespace App\Controller;

use App\Entity\Topic;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class ModeratorForumController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    // Méthode pour vérouiller un topic dans le forum d'un jeu
    #[Route('/moderator/forum/topic/{id}', name: 'app_moderator_forum_lock_topic')]
    public function lockTopic(Topic $topic, CsrfTokenManagerInterface $csrfTokenManager, Request $request): Response
    {
        if (!$topic) {
            throw $this->createNotFoundException('The topic does not exist.');
        }

        $token = new CsrfToken('lock_item', $request->request->get('_token'));

        if (!$csrfTokenManager->isTokenValid($token)) {
            throw $this->createAccessDeniedException('Token not valid');
        }

        $topic->setLocked(true);

        $this->entityManager->persist($topic);
        $this->entityManager->flush();

        $this->addFlash('success', 'The topic has been locked.');
        return $this->redirectToRoute('forum_game', ['id' => $topic->getGame()->getId(), 'slug' => $topic->getGame()->getSlug()]);
    }

    // Méthode pour dévérouiller un topic dans le forum d'un jeu
    #[Route('/moderator/forum/topic/{id}', name: 'app_moderator_forum_unlock_topic')]
    public function unlockTopic(Topic $topic, CsrfTokenManagerInterface $csrfTokenManager, Request $request): Response
    {
        if (!$topic) {
            throw $this->createNotFoundException('The topic does not exist.');
        }

        $token = new CsrfToken('unlock_item', $request->request->get('_token'));

        if (!$csrfTokenManager->isTokenValid($token)) {
            throw $this->createAccessDeniedException('Token not valid');
        }

        $topic->setLocked(false);

        $this->entityManager->persist($topic);
        $this->entityManager->flush();

        $this->addFlash('success', 'The topic has been unlocked.');
        return $this->redirectToRoute('forum_game', ['id' => $topic->getGame()->getId(), 'slug' => $topic->getGame()->getSlug()]);
    }
}
