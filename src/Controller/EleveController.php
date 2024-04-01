<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\ReservationCollectionType;
use App\Form\UserEleveType;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
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

#[Route('/{_locale}', requirements:["_locale" => "fr|en"])]
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
        $configDir = $this->getParameter('kernel.project_dir') . '/public/donnee';
        $filename = $configDir . '/limite_place_session.txt';
        $limite = file_get_contents($filename);
        return $limite;
    }    

    private function getMaxPlaceRDV():int
    {
        $configDir = $this->getParameter('kernel.project_dir') . '/public/donnee';
        $filename = $configDir . '/limite_places.txt';
        $limite = file_get_contents($filename);
        return $limite;
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
        $publicDir = $this->getParameter('kernel.project_dir') . '/public';
        $filename = $publicDir . '/donnee/num_etud.json';
        $filename2 = $publicDir . '/donnee/num_etud_orga.json';
        $num_etud = json_decode(file_get_contents($filename), true);
        $num_etud_orga = json_decode(file_get_contents($filename2), true);
        foreach ($num_etud_orga as $num) {
            if ($num_etud_compare == $num) {
                return 1;
            }
        }
        foreach ($num_etud as $num) {
            if ($num_etud_compare == $num) {
                return 2;
            }
        }
        return false;
    }

    #[Route('/inscription', name: 'inscription')]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // NOTE Vérification de la correspondance des mots de passe + hachage
            if ($form->get('plainPassword1')->getData() != $form->get('plainPassword2')->getData()) {
                $this->addFlash('danger', "Les mots de passe ne correspondent pas");
                return $this->redirectToRoute('inscription');
            }
            $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword1')->getData()));

            // NOTE Verification du nombre de place dans la session
            $nbre_session = $entityManager->getRepository(User::class)->count(['session' => $form->get('session')->getData()]);
            if ($nbre_session >= $this->getMaxPlaceSession()) {
                $this->addFlash('danger', "La session est complète");
                return $this->redirectToRoute('inscription');
            }
            // NOTE Vérification de l'existence du numéro étudiant
            $num_etud_exist = $this->getNumEtud($form->get('numetud')->getData());
            switch ($num_etud_exist) {
                case 1: 
                    $user->setNumEtud($form->get('numetud')->getData());
                    $user->setRoles(["ROLE_ORGANISATEUR"]);
                    $user->setType(3);
                    break;
                case 2:
                    $user->setNumEtud($form->get('numetud')->getData());
                    $user->setRoles(['ROLE_ELEVE']);
                    $user->setType(2);
                    break;
                default:
                    $this->addFlash('danger', "Numéro étudiant inconnu");
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
                $size = filesize($this->getParameter('kernel.project_dir').'/public/image_profil/personne_lambda.png');
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
            $this->addFlash('info', "Un mail de confirmation vous a été envoyé, Verifiez vos spams");
            return $this->redirectToRoute('accueil');
        }
        return $this->render('eleve/inscription_eleve.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    
    #[Route("/validation/{token}", name: "validation")]
    public function validation(String $token, UserRepository $urp): Response
    {
        $user = $urp->findOneBy(['token' => $token]);
        if ($user) {
            $user->setInfoValid(true);
            $user->setToken("");
            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Votre compte a été validé');
            return $this->redirectToRoute('connexion');
        }
        $this->addFlash('danger', 'Votre compte n\'a pas été validé');
        return $this->redirectToRoute('accueil');
    }

    #[Route ("/presentation/eleve/{nom}/{prenom}/{id}", name: "front_eleve")]
    public function presentationEleve(String $nom, String $prenom, int $id, UserRepository $urp): Response
    {   
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecter pour accéder à cette page');
            return $this->redirectToRoute('connexion');
        }
        $eleve = $urp->findOneBy(['nom' => $nom, 'prenom' => $prenom, "type" => 2, "id" => $id]);
        if (!$eleve) {
            $eleve = $urp->findOneBy(['nom' => $nom, 'prenom' => $prenom, "type" => 3, "id" => $id]);
        }
        if (!$eleve) {
            $this->addFlash('danger', 'Élève non trouvé');
            return $this->redirectToRoute('accueil');
        }
        return $this->render('eleve/presentation_eleve.html.twig', [
            'eleve' => $eleve
        ]);
    }

    #[Route ("/liste/eleve/{page<\d+>?1}", name: "front_liste_eleve")]
    public function listeEleve(UserRepository $urp, PaginatorInterface $paginator,int $page, Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecter pour accéder à cette page');
            return $this->redirectToRoute('connexion');
        }
        $eleves = $urp->findBy(['type' => 2]);
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
    public function editEleve(Request $request, EntityManagerInterface $eM): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('connexion');
        }
        else if ($user->getType() != 2 && $user->getType() != 3){
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour accéder à cette page');
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
            $this->addFlash('success', 'Profil modifié avec succès');
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
    public function reservation(Request $request, SessionRepository $srp): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('connexion');
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
                    $this->addFlash('danger', 'Vous avez déjà un rendez-vous à cette heure : '.$heure->format('H:i')." avec ce professionnel");
                    return $this->redirectToRoute('reservation');
                }
                $uniquerdv = $srp->findUniqueSessionProEleve($value->getPro()->getId(), $user->getId());
                if (count($uniquerdv) > 0) {
                    $this->addFlash('danger', 'Vous avez déjà un rendez-vous avec ce professionnel');
                    return $this->redirectToRoute('reservation');
                }
                $allrdv = $srp->findAllSessionProForOneHour($value->getPro()->getId(), $heure);
                if (count($allrdv) >= $this->getMaxPlaceRDV()) {
                    $this->addFlash('danger', 'Ce professionnel est déjà complet à cette heure');
                    return $this->redirectToRoute('reservation');
                }
                $value->setEleve($user);
                $value->setHeure($heure);
                $em = $this->doctrine->getManager();
                $em->persist($value);
                $em->flush();
            }
            $this->addFlash('success', 'Rendez-vous enregistré');
            return $this->redirectToRoute('accueil');
        }
        return $this->render('eleve/reservation.html.twig', [
            'user' => $user,
            'forms' => $form->createView(),
            'heures' => $heures,
        ]);
    }

    #[Route("/supprimer/{nom}/{prenom}/{id}", name: "supprimer_eleve_front")]
    public function supprimerEleve(int $id, String $nom, String $prenom, UserRepository $urp, SessionRepository $srp, Request $request, EntityManagerInterface $em, TokenStorageInterface $tokenStorage): Response
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
        $this->addFlash("success", "Le compte a été supprimé.");
        // Redirection vers l'accueil ou une page de confirmation
        return $this->redirectToRoute("accueil");
    }

}
