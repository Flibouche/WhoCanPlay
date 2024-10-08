<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        /**
         * @var User|null $user
         */
        // Cette annotation permet d'enlever le problème de reconnaissance de la méthode getEmail()
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $contact = $request->request->all();

            $email = (new TemplatedEmail())
                ->from($contact['email'])
                ->to('admin@whocanplay.com')
                ->subject($contact['subject'])
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
        ]);
    }
}
