<?php

namespace App\Controller;

use App\Entity\User;
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
        $filesystem = new Filesystem();
        $configDir = $this->getParameter('kernel.project_dir') . '/config';
        $filename = $configDir . '/limite_places.txt';
        if ($filesystem->exists($filename)) {
            $limite = file_get_contents($filename);
        } else {
            $limite = 0;
        }
        return $this->render('front/organisation.html.twig', [
            'limite' => $limite
        ]);
    }

    #[Route("/presentation/professionnel/{nom}/{prenom}/{id}", name: "front_pro")]
    public function presentationProfessionnel(String $nom, String $prenom, int $id, UserRepository $urp): Response
    {
        $pro = $urp->findOneBy(['nom' => $nom, 'prenom' => $prenom, "type" => 1, "id" => $id]);
        if ($pro === null) {
            throw $this->createNotFoundException('Professionnel non trouvé');
        }
        return $this->render('front/presentation_professionnel.html.twig', [
            'pro' => $pro
        ]);
    }

    #[Route ("/presentation/eleve/{nom}/{prenom}/{id}", name: "front_eleve")]
    public function presentationEleve(String $nom, String $prenom, int $id, UserRepository $urp): Response
    {
        $eleve = $urp->findOneBy(['nom' => $nom, 'prenom' => $prenom, "type" => 2, "id" => $id]);
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

    #[Route("/nous-trouvez", name: "carte")]
    public function carte(): Response
    {
        return $this->render('front/carte.html.twig');
    }

    #[Route ("inscription/pro/{token}", name : "inscription_pro")]
    public function inscriptionPro (String $token, Request $request,  UserRepository $urp, UserPasswordHasherInterface $userPasswordHasher)
    {
        $user = $urp->findOneBy(['token' => $token]);
        $form = $this->createForm(RegistrationProFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setInfoValid(true);
            $user->setToken("");
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Votre compte a bien été créé, vous pouvez vous connecter');
            return $this->redirectToRoute('connexion');
        }

        return $this->render('registration/inscription_pro.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route ("/liste/professionnel", name: "liste_pro")]
    public function listePro(UserRepository $urp): Response
    {
        $pros = $urp->findBy(['type' => 1]);
        return $this->render('front/liste_pro.html.twig', [
            'pros' => $pros
        ]);
    }

    #[Route ("/liste/eleve/{page<\d+>?1}", name: "front_liste_eleve")]
    public function listeEleve(UserRepository $urp, PaginatorInterface $paginator,int $page, Request $request): Response
    {
        $eleves = $urp->findBy(['type' => 2]);
        $pagination = $paginator->paginate($eleves, $request->query->getInt('page', $page), 10);
        return $this->render('front/liste_eleve.html.twig', [
            'eleves' => $pagination
        ]);
    }
}
