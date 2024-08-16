<?php

namespace App\Controller\Admin;

use App\Entity\Game;
use App\Entity\Topic;
use App\Form\TopicType;
use App\Repository\GameRepository;
use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin-{secret}/topic', name: 'app_admin_topic_')]
#[IsGranted('ROLE_ADMIN')]
class TopicAdminController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // Méthode pour afficher la liste des sujets
    #[Route('/', name: 'show')]
    #[IsGranted('ROLE_ADMIN')]
    public function showTopics(string $secret, TopicRepository $topicRepository): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        $topics = $topicRepository->findBy([], ['id' => 'DESC']);

        return $this->render('admin/topics/show.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'topics' => $topics,
        ]);
    }

    // Méthode pour afficher la liste des jeux pour créer un topic
    #[Route('/create', name: 'create')]
    #[IsGranted('ROLE_ADMIN')]
    public function createTopic(string $secret, GameRepository $gameRepository): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        $activeGames = $gameRepository->findBy(['status' => 1], ['slug' => 'ASC']);

        return $this->render('admin/topics/create.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'games' => $activeGames,
        ]);
    }

    // Méthode pour supprimer un sujet
    #[Route('/delete/{id}', name: 'delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteTopic(string $secret, Topic $topic): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$topic) {
            throw $this->createNotFoundException('No topic found');
        }

        $this->entityManager->remove($topic);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_topic_show', ['secret' => $secret]);
    }

    // Méthode pour verrouiller un sujet
    #[Route('/lock/{id}', name: 'lock')]
    #[IsGranted('ROLE_ADMIN')]
    public function lockTopic(string $secret, Topic $topic): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$topic) {
            throw $this->createNotFoundException('No topic found');
        }

        $topic->setLocked(true);
        $this->entityManager->persist($topic);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_topic_show', ['secret' => $secret]);
    }

    // Méthode pour déverrouiller un sujet
    #[Route('/unlock/{id}', name: 'unlock')]
    #[IsGranted('ROLE_ADMIN')]
    public function unlockTopic(string $secret, Topic $topic): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$topic) {
            throw $this->createNotFoundException('No topic found');
        }

        $topic->setLocked(false);
        $this->entityManager->persist($topic);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_admin_topic_show', ['secret' => $secret]);
    }
}
