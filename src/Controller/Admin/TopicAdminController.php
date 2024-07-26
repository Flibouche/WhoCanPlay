<?php

namespace App\Controller\Admin;

use App\Repository\TopicRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    #[Route('/', name: 'show')]
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

    public function deleteTopic(string $secret): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->redirectToRoute('app_admin_topic_show', ['secret' => $secret]);
    }
}