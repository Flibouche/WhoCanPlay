<?php

namespace App\Controller\Admin;

use App\Entity\Topic;
use App\Form\TopicType;
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

    // Méthode pour afficher les détails d'un sujet
    #[Route('/details/{id}', name: 'details')]
    #[IsGranted('ROLE_ADMIN')]
    public function detailsTopic(string $secret, Topic $topic): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->render('admin/topics/details.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'topic' => $topic,
        ]);
    }

    // Méthode pour créer ou modifier un sujet
    #[Route('/create', name: 'create')]
    #[Route('/edit/{id}', name: 'edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function createOrEditTopic(string $secret, ?Topic $topic, Request $request): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$topic) {
            $topic = new Topic();
        }

        $form = $this->createForm(TopicType::class, $topic);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($topic);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_topic_show', ['secret' => $secret]);
        }

        return $this->render('admin/topics/create.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'formAddTopic' => $form,
            'topic' => $topic,
            'edit' => $topic->getId(),
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
}
