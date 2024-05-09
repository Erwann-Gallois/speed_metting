<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Variable;
use App\Form\ContactFormType;
use App\Form\QuestionProType;
use App\Form\RegistrationProFormType;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class FrontController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $doctrine, 
        private Security $security, 
    )
    {
    }
    
    #[Route('/', name: 'accueil')]
    public function index(Request $request, UserRepository $urp): Response
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
        $pros = $urp->rand();
        return $this->render('front/index.html.twig', [
            'form' => $form,
            'pros' => $pros
            // 'afficherlien' => $afficherLien,
        ]);
    }

    #[Route('/contact/{nom}/{email}/{sujet}/{message}', name: 'mail_contact')]
    public function contact(String $nom, String $email, String $sujet, String $message, MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        $message = (new TemplatedEmail())
            ->from($email)
            ->to('20210472@etud.univ-evry.fr')
            ->subject('Contact Site Speed Meetings - '.$sujet)
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
        $this->addFlash('success', $translator->trans('flash.send_mail'));
        return $this->redirectToRoute('accueil');
    }

    #[Route('/organisation', name: 'organisation')]
    public function organisation(): Response
    {
        $limite = $this->doctrine->getRepository(Variable::class)->findOneBy(['id' => 1]);
        $limite = $limite->getPlaceRdv();
        return $this->render('front/organisation.html.twig', [
            'limite' => $limite
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

    #[Route("/nous-trouvez", name: "carte")]
    public function carte(): Response
    {
        return $this->render('front/carte.html.twig');
    }

    #[Route('/compte', name: 'compte')]
    public function redirection_compte(): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('connexion');
        }
        else if ($user->getType() == 1) {
            return $this->redirectToRoute('compte_pro');
        }
        else if ($user->getType() == 2 || $user->getType() == 3)
        {
            return $this->redirectToRoute('compte_eleve');
        }
    }

    #[Route("/compte/planning", name: "planning")]
    public function redirection_planning():Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('connexion');
        }
        else if ($user->getType() == 1) {
            return $this->redirectToRoute('planning_pro', ['id' => $user->getId(), 'nom' => $user->getNom(), 'prenom' => $user->getPrenom()]);
        }
        else if ($user->getType() == 2 || $user->getType() == 3){
            return $this->redirectToRoute('planning_eleve');
        }
    }

}
