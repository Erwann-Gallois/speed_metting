<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\User;
use App\Form\DateReservationType;
use App\Form\EmailSelectionType;
use App\Form\LimitePlacesFormType;
use App\Form\LimiteSessionFormType;
use App\Form\ProfessionnelType;
use App\Repository\UserRepository;
use DateTime;
use DateTimeZone;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
// use Symfony\Component\CssSelector\XPath\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ORGANISATEUR')]
class AdminController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $doctrine, 
        private Security $security, 
    )
    {
    }
    #[Route('', name: 'admin')]
    public function index(Request $request, UserRepository $urp): Response
    {
        $nbre_pro = count($urp->findBy(["type" => 1]));
        $nbre_eleve = count($urp->findBy(["type" => 2]));
        $nbre_organisateur = count($urp->findBy(["type" => 3]));
        $form = $this->createForm(LimitePlacesFormType::class);
        $form->handleRequest($request);

        $form2 = $this->createForm(LimiteSessionFormType::class);
        $form2->handleRequest($request);

        $form3 = $this->createForm(DateReservationType::class);
        $form3->handleRequest($request);
        // Mettre à jour le fichier de configuration ou un fichier spécifique
        $filesystem = new Filesystem();
        $configDir = $this->getParameter('kernel.project_dir') . '/config';
        $filename = $configDir . '/limite_places.txt';
        $filename2 = $configDir . '/limite_place_session.txt';
        $filename3 = $configDir . '/date_reservation.txt';
        if ($form->isSubmitted() && $form->isValid()) {
            $limitePlaces = $form->get('limite_places')->getData();

            // Mettre à jour le fichier de configuration ou un fichier spécifique
            $filesystem = new Filesystem();
            $configDir = $this->getParameter('kernel.project_dir') . '/config';
            $filename = $configDir . '/limite_places.txt';

            try {
                $filesystem->dumpFile($filename, $limitePlaces);
                $this->addFlash('success', 'La limite de places a été mise à jour.');
            } catch (IOExceptionInterface $exception) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de la limite de places.');
            }

            return $this->redirectToRoute('admin');
        }

        if ($form2->isSubmitted() && $form2->isValid()) {
            $limiteSession = $form2->get('limite_session')->getData();

            // Mettre à jour le fichier de configuration ou un fichier spécifique
            $filesystem = new Filesystem();
            $configDir = $this->getParameter('kernel.project_dir') . '/config';
            $filename2 = $configDir . '/limite_place_session.txt';

            try {
                $filesystem->dumpFile($filename2, $limiteSession);
                $this->addFlash('success', 'La limite de places par session a été mise à jour.');
            } catch (IOExceptionInterface $exception) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de la limite de places par session.');
            }

            return $this->redirectToRoute('admin');
        }

        if ($form3->isSubmitted() && $form3->isValid())
        {
            $date = $form3->get('date')->getData();
            // $date = new DateTime($date, new DateTimeZone("Europe/Paris"));
            $filesystem = new Filesystem();
            $configDir = $this->getParameter('kernel.project_dir') . '/config';
            $filename3 = $configDir . '/date_reservation.txt';
            
            try {
                $filesystem->dumpFile($filename3, $date->format("d/m/Y H:i"));
                $this->addFlash('success', 'La date de reservation a été mise à jour.');
            } catch (IOExceptionInterface $exception) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de la date de réservation.');
            }

            return $this->redirectToRoute('admin');

        }
        // Lire la valeur actuelle
        $nbre_place_groupe = null;
        if ($filesystem->exists($filename)) {
            $nbre_place_groupe = file_get_contents($filename);
        }
        $nbre_place_session = null;
        if ($filesystem->exists($filename2)) {
            $nbre_place_session = file_get_contents($filename2);
        }
        $date = null;
        if ($filesystem->exists($filename3)) {
            $date = file_get_contents($filename3);
        }
        return $this->render('admin/index.html.twig', [
            'form' => $form->createView(),
            'form2' => $form2->createView(),
            'form3' => $form3->createView(),
            'nbre_place_groupe' => $nbre_place_groupe,
            'nbre_place_session' => $nbre_place_session,
            'date' => $date,
            'nbre_eleve' => $nbre_eleve + $nbre_organisateur,
            'nbre_pro' => $nbre_pro,
        ]);
    }

    #[Route('/liste/professionnel', name: 'liste_professionnel')]
    public function listeProfessionnel(Request $request): Response
    {
        $pros = $this->doctrine->getRepository(User::class)->findBy(["type" => 1]);
        // dump($pro);
        $emails = array_map(function ($pro) {
            return $pro->getEmail();
        }, $pros);
        $form = $this->createForm(EmailSelectionType::class, null, ['emails' => $emails]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $selectedEmails = [];
            foreach ($emails as $index => $email) {
                $checkboxField = 'email_' . $index;
                if ($form->has($checkboxField) && $form->get($checkboxField)->getData() === true) {
                    $selectedEmails[] = $email;
                }
            }
            dump($selectedEmails); // Cela devrait maintenant vous montrer les e-mails sélectionnés
        }
        return $this->render('admin/liste_professionnel.html.twig', [
            'pros' => $pros,
            'form' => $form->createView(),
            // 'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/liste/eleve', name: 'liste_eleve')]
    public function listeEleve(Request $request): Response
    {
        $eleves = $this->doctrine->getRepository(User::class)->findBy(["type" => 2]);
        $nbre_session = [];
        foreach ($eleves as $key => $value) {
            $nbre_session[$key] = count($this->doctrine->getRepository(Session::class)->findAllSessionEleve($value->getId()));
        }
        // dump($nbre_session);
        $emails = array_map(function ($eleve) {
            return $eleve->getEmail();
        }, $eleves);
        $form = $this->createForm(EmailSelectionType::class, null, ['emails' => $emails]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $selectedEmails = [];
            foreach ($emails as $index => $email) {
                $checkboxField = 'email_' . $index;
                if ($form->has($checkboxField) && $form->get($checkboxField)->getData() === true) {
                    $selectedEmails[] = $email;
                }
            }
            dump($selectedEmails); // Cela devrait maintenant vous montrer les e-mails sélectionnés
        }
        return $this->render('admin/liste_eleve.html.twig', [
            'eleves' => $eleves,
            'form' => $form->createView(),
            'nbre_session' => $nbre_session,
            // 'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/liste/organisateur', name: 'liste_organisateur')]
    public function listeorganisateur(Request $request): Response
    {
        $organisateurs = $this->doctrine->getRepository(User::class)->findBy(["type" => 3]);
        // dump($organisateur);
        $emails = array_map(function ($organisateur) {
            return $organisateur->getEmail();
        }, $organisateurs);
        $form = $this->createForm(EmailSelectionType::class, null, ['emails' => $emails]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $selectedEmails = [];
            foreach ($emails as $index => $email) {
                $checkboxField = 'email_' . $index;
                if ($form->has($checkboxField) && $form->get($checkboxField)->getData() === true) {
                    $selectedEmails[] = $email;
                }
            }
            dump($selectedEmails); // Cela devrait maintenant vous montrer les e-mails sélectionnés
        }
        return $this->render('admin/liste_organisateur.html.twig', [
            'organisateurs' => $organisateurs,
            'form' => $form->createView(),
            // 'controller_name' => 'AdminController',
        ]);
    }

    #[Route ("creation/professionnel", name : "creer_pro")]
    public function creer_pro (Request $request, UserPasswordHasherInterface $hasher, MailerInterface $mailer, UserRepository $urp)
    {
        $form = $this->createForm(ProfessionnelType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();
            $verif = $urp->findBy(["email" => $data['email']]);
            if (count($verif) > 0)
            {
                $this->addFlash('warning', "Cette adresse mail est déjà associé à un utilisateur");
                return $this->redirectToRoute("admin");
            }
            $user = new User();
            $user->setNom($data["nom"]);
            $user->setPrenom($data["prenom"]);
            $user->setEmail($data["email"]);
            $user->setRoles(["ROLE_PROFESSIONNEL"]);
            $user->setType(1);
            $user->setPoste($data["poste"]);
            $password = $hasher->hashPassword($user, "1234");
            $user->setPassword($password);
            $token = uniqid('', true);
            $user->setToken($token);
            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();

            $message = (new TemplatedEmail())
            ->from(new Address("no.reply.speed.meetings2024@gmail.com", "Speed Meetings 2024"))
            ->to($data["email"])
            ->subject("Lien de connexion pour remplir le formulaire de présentation")
            // path of the Twig template to render
            ->htmlTemplate('mail/lien_connexion.html.twig')
            // pass variables (name => value) to the template
            ->context([
                "user" => $user,
                "token" => $token
            ]);
            $mailer->send($message);
            $this->addFlash("success", "Le professionnel a été creer et l'email a été envoyer");
            return $this->redirectToRoute("admin");
        }
        return $this->render("admin/creer_pro.html.twig", [
            "form" => $form->createView(),
        ]);
    }   
}
