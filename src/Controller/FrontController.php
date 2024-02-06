<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ContactFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $doctrine, 
        private Security $security, 
    )
    {
    }
    
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
        $message = (new TemplatedEmail())
            ->from($email)
            ->to('no.reply.speed.meetings2024@gmail.com')
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

    #[Route("/presentation/professionnel/{nom}/{prenom}/{id}", name: "front_pro")]
    public function presentationProfessionnel(String $nom, String $prenom, int $id): Response
    {
        $pro = $this->doctrine->getRepository(User::class)->findOneBy(['nom' => $nom, 'prenom' => $prenom, "type" => 1, "id" => $id]);
        if ($pro === null) {
            throw $this->createNotFoundException('Professionnel non trouvé');
        }
        return $this->render('front/presentation_professionnel.html.twig', [
            'pro' => $pro
        ]);
    }

    #[Route ("/presentation/eleve/{nom}/{prenom}/{id}", name: "front_eleve")]
    public function presentationEleve(String $nom, String $prenom, int $id): Response
    {
        $eleve = $this->doctrine->getRepository(User::class)->findOneBy(['nom' => $nom, 'prenom' => $prenom, "type" => 2, "id" => $id]);
        return $this->render('front/presentation_eleve.html.twig', [
            'eleve' => $eleve
        ]);
    }

    #[Route("/contact", name: "contact")]
    public function page_contact(Request $request): Response
    {   
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
        return $this->render('front/contact.html.twig', 
        [
            'form' => $form
        ]);
    }

}
