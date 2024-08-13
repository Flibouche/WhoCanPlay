<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        /**
         * @var User|null $user
         */
        // Cette annotation permet d'enlever le problème de reconnaissance de la méthode getEmail()
        $user = $this->getUser();
        $contact = new Contact();

        if ($user) {
            $contact->setEmail($user->getEmail());
        }

        $form = $this->createForm(ContactType::class, $contact);
        $form = $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contact = $form->getData();

            $entityManager->persist($contact);
            $entityManager->flush();

            $email = (new TemplatedEmail())
                ->from($contact->getEmail())
                ->to('admin@whocanplay.com')
                ->subject($contact->getSubject())
                ->htmlTemplate('emails/application.html.twig')
                ->context([
                    'contact' => $contact,
                ]);

            $mailer->send($email);

            $this->addFlash('success', 'Your message has been sent successfully!');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('contact/contact.html.twig', [
            'controller_name' => 'ContactController',
            'formContact' => $form,
        ]);
    }
}
