<?php

namespace App\Controller\Admin;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
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
    #[IsGranted('ROLE_ADMIN')]
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

    #[Route('/details/{id}', name: 'details')]
    #[IsGranted('ROLE_ADMIN')]
    public function detailsContact(string $secret, Contact $contact): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        return $this->render('admin/contacts/details.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'contact' => $contact,
        ]);
    }

    #[Route('/create', name: 'create')]
    #[Route('/edit/{id}', name: 'edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function createOrEditContact(string $secret, ?Contact $contact, Request $request): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        if (!$contact) {
            $contact = new Contact();
            
            /**
             * @var User|null $user
             */
            // Cette annotation permet d'enlever le problème de reconnaissance de la méthode getEmail()
            $user = $this->getUser();
            if ($user) {
                $contact->setEmail($user->getEmail());
            }
        }

        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $contact = $form->getData();
            $this->entityManager->persist($contact);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_admin_contact_show', ['secret' => $secret]);
        }

        return $this->render('admin/contacts/create.html.twig', [
            'controller_name' => 'FeatureAdminController',
            'formAddContact' => $form,
            'contact' => $contact,
            'edit' => $contact->getId(),
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteContact(string $secret, Contact $contact): Response
    {
        $expectedSecret = $this->getParameter('admin_secret');
        if ($secret !== $expectedSecret) {
            throw $this->createAccessDeniedException('Page not found');
        }

        $em = $this->entityManager;

        if (!$contact) {
            throw $this->createNotFoundException('Contact not found');
        }

        $em->remove($contact);
        $em->flush();

        return $this->redirectToRoute('app_admin_contact_show', ['secret' => $secret]);
    }
}
