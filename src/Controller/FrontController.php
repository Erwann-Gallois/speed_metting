<?php

namespace App\Controller;

use App\Form\ContactFormType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    #[Route('/', name: 'accueil')]
    public function index(Request $request): Response
    {
        //Formulaire de contact
        $form = $this->createForm(ContactFormType::class, null, ['method' => 'POST']);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { 
            $contactFormData = $form->getData();
            // $this->addFlash('success', 'Votre message a été envoyé');
            return $this->redirectToRoute('mail_contact', [
                'nom' => $contactFormData["nom"],
                'email' => $contactFormData["email"],
                'sujet' => $contactFormData["sujet"],
                'message' => $contactFormData["message"]
            ]);
        }
        return $this->render('front/index.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/contact/{nom}/{email}/{sujet}/{message}', name: 'mail_contact')]
    public function contact(String $nom, String $email, String $sujet, String $message, MailerInterface $mailer): Response
    {
        dump($nom, $email, $sujet, $message);
        $message = (new TemplatedEmail())
            ->from($email)
            ->to('lapatteprecieuse0@gmail.com')
            ->subject('Contact - '.$sujet)
            // path of the Twig template to render
            ->htmlTemplate('mail/contact.html.twig')
            // pass variables (name => value) to the template
            ->context([
                'nom' => $nom,
                'adresse' => $email,
                'sujet' => $sujet,
                'message' => $message
            ]);
        $mailer->send($message);
        $this->addFlash('success', 'Votre message a été envoyé');
        return $this->redirectToRoute('accueil');
    }

    #[Route('/organisation', name: 'organisation')]
    public function organisation(): Response
    {
        return $this->render('front/organisation.html.twig');
    }

}
