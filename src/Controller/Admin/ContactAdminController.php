<?php

namespace App\Controller\Admin;

use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin-{secret}/contact', name: 'app_admin_contact_')]
#[IsGranted('ROLE_ADMIN')]
class ContactAdminController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'show')]
    public function showContacts(string $secret, ContactRepository $contactRepository): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        $contacts = $contactRepository->findBy([], ['id' => 'DESC']);

        return $this->render('admin/contacts/show.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'contacts' => $contacts,
        ]);
    }
}