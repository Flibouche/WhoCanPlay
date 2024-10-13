<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(Request $request, MailerInterface $mailer, CsrfTokenManagerInterface $csrfTokenManager, ValidatorInterface $validator): Response
    {
        $errors = [];

        if ($request->isMethod('POST')) {
            $token = $request->request->get('_csrf_token');
            if (!$csrfTokenManager->isTokenValid(new CsrfToken('contact', $token))) {
                throw $this->createAccessDeniedException('Invalid CSRF token.');
            }

            $email = trim($request->request->get('email'));
            $subject = trim($request->request->get('subject'));
            $message = trim($request->request->get('message'));

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please enter a valid email address.';
            }

            if (empty($subject)) {
                $errors['subject'] = 'Please enter a subject.';
            }

            if (empty($message)) {
                $errors['message'] = 'Please enter a message.';
            }

            if (empty($errors)) {
                $email = (new TemplatedEmail())
                    ->from($email)
                    ->to('admin@whocanplay.com')
                    ->subject($subject)
                    ->htmlTemplate('emails/application.html.twig')
                    ->html(sprintf(
                        '<p>%s</p>',
                        nl2br(htmlspecialchars($message))
                    ));

                try {
                    $mailer->send($email);
                    $this->addFlash('success', 'Your message has been sent successfully!');
                    return $this->redirectToRoute('app_home');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'There was an error sending your message. Please try again later.');
                }
            } else {
                // Ajouter un flash message pour les erreurs globales si nécessaire
                $this->addFlash('error', 'Please correct the errors below.');
            }

            // Passer les erreurs au template
            return $this->render('contact/contact.html.twig', [
                'errors' => $errors,
            ]);
        }

        return $this->render('contact/contact.html.twig', [
            'controller_name' => 'ContactController',
            'errors' => [],
        ]);
    }
}
