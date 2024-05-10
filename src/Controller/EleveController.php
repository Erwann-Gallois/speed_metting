<?php

namespace App\Controller;

use App\Entity\NumeroEtudiant;
use App\Entity\Session;
use App\Entity\User;
use App\Entity\Variable;
use App\Form\RegistrationFormType;
use App\Form\ReservationCollectionType;
use App\Form\UserEleveType;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class EleveController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $doctrine, 
        private Security $security, 
    )
    {
    }

    private function getMaxPlaceSession():int
    {
        $variable = $this->doctrine->getRepository(Variable::class)->find(1);
        return $variable->getPlaceSession();
    }    

    private function getMaxPlaceRDV(int $session):int
    {
        $variable = $this->doctrine->getRepository(Variable::class)->find(1);
        if ($session == 1) {
            return $variable->getPlaceRdv();
        }
        else {
            return $variable->getPlaceRdv2();
        }
    }

    private function slotRDV(int $session):array
    {
        $slots = [];
        $sessions = [
            1 => ['14:00', '15:00'],
            2 => ['16:00', '17:00'],
        ];

        list($start, $end) = $sessions[$session];
        $start = new \DateTime($start);
        $end = new \DateTime($end);
        $i = 0;
        while ($start < $end) {
            $time = $start->format('H:i');
            $slots[$i] = $time;
            $start->modify('+10 minutes');
            $i++;
        }
        return $slots;
    }

    private function getNumEtud(int $num_etud_compare):int|bool
    {
        $num_etud = $this->doctrine->getRepository(NumeroEtudiant::class)->findOneBy(['numero' => $num_etud_compare]);
        if ($num_etud != null) {
           return $num_etud->getType();
        }
        return false;
    }

    private function getDateFinInscription():bool
    {
        $variable = $this->doctrine->getRepository(Variable::class)->find(1);
        $date = $variable->getDateFinInscription();
        $now = new DateTime();
        return $now >= $date;
    }

    private function getDateOuvertureRDV():bool
    {
        $variable = $this->doctrine->getRepository(Variable::class)->find(1);
        $date = $variable->getDateOuverResa();
        $now = new DateTime();
        return $now >= $date;
    }

    private function getDateFinRDV():bool
    {
        $variable = $this->doctrine->getRepository(Variable::class)->find(1);
        $date = $variable->getDateFinResa();
        $now = new DateTime();
        return $now >= $date;
    }

    #[Route('/inscription', name: 'inscription')]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher, MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        if ($this->getDateFinInscription())
        {
            $this->addFlash('danger', $translator->trans("flash.inscription_fin"));
            return $this->redirectToRoute('accueil');
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // NOTE Vérification de la correspondance des mots de passe + hachage
            if ($form->get('plainPassword1')->getData() != $form->get('plainPassword2')->getData()) {
                $this->addFlash('danger', $translator->trans("flash.mdp_dif"));
                return $this->redirectToRoute('inscription');
            }
            $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword1')->getData()));

            // NOTE Verification du nombre de place dans la session
            $nbre_session = $entityManager->getRepository(User::class)->count(['session' => $form->get('session')->getData(), "type" => 2]);
            if ($nbre_session >= $this->getMaxPlaceSession()) {
                $this->addFlash('danger', $translator->trans("flash.session_complet"));
                return $this->redirectToRoute('inscription');
            }
            // NOTE Vérification de l'existence du numéro étudiant
            $num_etud_exist = $this->getNumEtud($form->get('numetud')->getData());
            switch ($num_etud_exist) {
                case 1: 
                    $user->setNumEtud($form->get('numetud')->getData());
                    $user->setRoles(['ROLE_ELEVE']);
                    $user->setType(2);
                    break;
                case 2:
                    $user->setNumEtud($form->get('numetud')->getData());
                    $user->setRoles(["ROLE_ORGANISATEUR"]);
                    $user->setType(3);
                    break;
                default:
                    $this->addFlash('danger', $translator->trans("flash.numetud_wrong"));
                    return $this->redirectToRoute('inscription');
                    break;
            }
            
            // NOTE Vérification de l'image de profil
            if ($form->get('imageFile')->getData() != null) {
                $file = $form->get('imageFile')->getData();
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $file->move($this->getParameter('kernel.project_dir').'/public/image_profil', $fileName);
                $size = filesize($this->getParameter('kernel.project_dir').'/public/image_profil/'.$fileName);
                $user->setImageName($fileName);
                $user->setImageSize($size);
            }
            else {
                $user->setImageName('personne_lambda.png');
                $size = filesize($this->getParameter('kernel.project_dir').'/public/images/personne_lambda.png');
                $user->setImageSize($size);
            }
            $token = uniqid('', true);
            $user->setToken($token);
            $entityManager->persist($user);
            $entityManager->flush();
            $message = (new TemplatedEmail())
            ->from(new Address("no.reply.speed.meetings2024@univ-evry.fr", "Speed Meetings 2024"))
            ->to($user->getEmail())
            ->subject("Validation de votre compte Speed Meetings 2024")
            // path of the Twig template to render
            ->htmlTemplate('mail/lien_validation.html.twig')
            // pass variables (name => value) to the template
            ->context([
                "user" => $user,
                "token" => $token
            ]);
            $mailer->send($message);
            $this->addFlash('info', $translator->trans("flash.mail_valid"));
            return $this->redirectToRoute('accueil');
        }
        return $this->render('eleve/inscription_eleve.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    
    #[Route("/validation/{token}", name: "validation")]
    public function validation(String $token, UserRepository $urp, TranslatorInterface $translator): Response
    {
        $user = $urp->findOneBy(['token' => $token]);
        if ($user) {
            $user->setInfoValid(true);
            $user->setToken("");
            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', $translator->trans("flash.compte_valid"));
            return $this->redirectToRoute('connexion');
        }
        $this->addFlash('danger', $translator->trans("flash.compte_non_valid"));
        return $this->redirectToRoute('accueil');
    }

    #[Route ("/presentation/eleve/{nom}/{prenom}/{id}", name: "front_eleve")]
    public function presentationEleve(String $nom, String $prenom, int $id, UserRepository $urp, TranslatorInterface $translator): Response
    {   
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('warning', $translator->trans("flash.no_connect"));
            return $this->redirectToRoute('connexion');
        }
        $eleve = $urp->findOneBy(['nom' => $nom, 'prenom' => $prenom, "type" => 2, "id" => $id]);
        if (!$eleve) {
            $eleve = $urp->findOneBy(['nom' => $nom, 'prenom' => $prenom, "type" => 3, "id" => $id]);
        }
        if (!$eleve) {
            $this->addFlash('danger', $translator->trans("flash.student_not_found"));
            return $this->redirectToRoute('accueil');
        }
        return $this->render('eleve/presentation_eleve.html.twig', [
            'eleve' => $eleve
        ]);
    }

    #[Route ("/liste/eleve/{page<\d+>?1}", name: "front_liste_eleve")]
    public function listeEleve(UserRepository $urp, PaginatorInterface $paginator,int $page, Request $request, TranslatorInterface $translator): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('warning', $translator->trans("flash.no_connect"));
            return $this->redirectToRoute('connexion');
        }
        $eleves = $urp->findBy(['type' => 2]);
        $orga = $urp->findBy(['type' => 3]);
        $eleves = array_merge($eleves, $orga);
        $pagination = $paginator->paginate($eleves, $request->query->getInt('page', $page), 10);
        return $this->render('eleve/liste_eleve.html.twig', [
            'eleves' => $pagination
        ]);
    }

    #[Route('/compte/eleve', name: 'compte_eleve')]
    #[IsGranted('ROLE_ELEVE')]
    public function compteEleve(): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('connexion');
        }
        elseif ($user->getType() == 1){
            return $this->redirectToRoute('accueil');
        }
        return $this->render('eleve/compte_eleve.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/compte/eleve/modifier', name: 'eleve_modifier')]
    public function editEleve(Request $request, EntityManagerInterface $eM, TranslatorInterface $translator): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('connexion');
        }
        else if ($user->getType() != 2 && $user->getType() != 3){
            $this->addFlash('danger', $translator->trans("flash.not_access"));
            return $this->redirectToRoute('compte');
        }
        $form = $this->createForm(UserEleveType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $data = $form->getData();
            if ($form->get("imageFile")->getData() != null) {
                $name = $user->getImageName();
                if ($name != "personne_lambda.png") {
                    unlink($this->getParameter('kernel.project_dir').'/public/image_profil/'.$name);
                }
                $file = $form->get('imageFile')->getData();
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $file->move($this->getParameter('kernel.project_dir').'/public/image_profil', $fileName);
                $size = filesize($this->getParameter('kernel.project_dir').'/public/image_profil/'.$fileName);
                $user->setImageName($fileName);
                $user->setImageSize($size);
            }
            $eM->persist($user);
            $eM->flush();
            $this->addFlash('success', $translator->trans("flash.modf_profil"));
            return $this->redirectToRoute('compte');
        }
        return $this->render('eleve/edit_eleve.html.twig',[
            'form'=> $form->createView(),
            'user' => $user
        ]);
    }

    #[Route("/compte/eleve/planning", name: "planning_eleve")]
    #[IsGranted('ROLE_ELEVE')]
    public function planningEleve(SessionRepository $srp):Response
    {
        $user = $this->security->getUser();
        $sessions = $srp->findAllSessionEleve($user->getId());
        if (!$user) {
            return $this->redirectToRoute('connexion');
        }
        else if ($user->getType() == 1) {
            return $this->redirectToRoute('planning');
        }
        return $this->render('eleve/planning_eleve.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user,
            'sessions' => $sessions
        ]);
    }

    #[Route('/reservation', name: 'reservation')]
    public function reservation(Request $request, SessionRepository $srp, TranslatorInterface $translator): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('connexion');
        }
        if ($this->getDateOuvertureRDV() == false)
        {
            $this->addFlash('warning', $translator->trans("flash.rdvnotopen"));
            return $this->redirectToRoute("accueil");
        }
        if ($this->getDateFinRDV() == true)
        {
            $this->addFlash('info', $translator->trans("flash.rdvfinish"));
            return $this->redirectToRoute("accueil");
        }
        $session = $user->getSession();
        $heures = $this->slotRDV($session);
        $reservation = array();
        for ($i = 0; $i < count($heures); $i++) {
            $reservation['reservation'][$i] = new Session();
        }
        $form = $this->createForm(ReservationCollectionType::class, $reservation);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            foreach ($data['reservation'] as $key => $value) {
                $heure = new \DateTime($heures[$key]);
                $unique = $srp->findUniqueSession($value->getPro()->getId(), $heure, $user->getId());
                if (count($unique) > 0) {
                    $this->addFlash('danger', $translator->trans("flash.rdvalreadywithpro") . $heure->format("H:i:s")) ;
                    return $this->redirectToRoute('reservation');
                }
                $uniquerdv = $srp->findUniqueSessionProEleve($value->getPro()->getId(), $user->getId());
                if (count($uniquerdv) > 0) {
                    $this->addFlash('danger', $translator->trans("flash.rdvalreadytake"));
                    return $this->redirectToRoute('reservation');
                }
                $allrdv = $srp->findAllSessionProForOneHour($value->getPro()->getId(), $heure);
                if (count($allrdv) >= $this->getMaxPlaceRDV($session)) {
                    $this->addFlash('danger', $translator->trans("flash.rdvfull"));
                    return $this->redirectToRoute('reservation');
                }
                $value->setEleve($user);
                $value->setHeure($heure);
                $value->setDateReservation(new DateTime('now'));
                $em = $this->doctrine->getManager();
                $em->persist($value);
                $em->flush();
            }
            $this->addFlash('success', $translator->trans("flash.rdvgood"));
            return $this->redirectToRoute('accueil');
        }
        return $this->render('eleve/reservation.html.twig', [
            'user' => $user,
            'forms' => $form->createView(),
            'heures' => $heures,
        ]);
    }

    #[Route("/supprimer/{nom}/{prenom}/{id}", name: "supprimer_eleve_front")]
    public function supprimerEleve(int $id, String $nom, String $prenom, UserRepository $urp, SessionRepository $srp, Request $request, TranslatorInterface $translator, EntityManagerInterface $em, TokenStorageInterface $tokenStorage): Response
    {
        $eleve = $urp->findOneBy(['id' => $id, 'type' => 2, 'nom' => $nom, 'prenom' => $prenom]);
        if (!$eleve) {
            $eleve = $urp->findOneBy(['id' => $id, 'type' => 3, 'nom' => $nom, 'prenom' => $prenom]);
        }
        if (!$eleve) {
            $this->addFlash("error", "Élève non trouvé.");
            return $this->redirectToRoute("accueil");
        }
        // Avant la suppression, déconnectez l'utilisateur si c'est l'utilisateur actuellement connecté
        if ($this->getUser() && $this->getUser()->getId() === $eleve->getId()) {
            // Déconnexion de l'utilisateur
            $tokenStorage->setToken(null);
            $request->getSession()->invalidate();
        }
        // Logique de suppression de l'utilisateur ici
        $name = $eleve->getImageName();
        if ($name != "personne_lambda.png" && $name != null) {
            unlink($this->getParameter('kernel.project_dir').'/public/image_profil/'.$name);
        }
        $sessionsEleve = $srp->findAllSessionEleve($eleve->getId());
        foreach ($sessionsEleve as $session) {
            $em->remove($session);
        }
        $em->remove($eleve);
        $em->flush();
        $this->addFlash("success", $translator->trans("flash.supp_compte"));
        // Redirection vers l'accueil ou une page de confirmation
        return $this->redirectToRoute("accueil");
    }

}
