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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
        $configDir = $this->getParameter('kernel.project_dir') . '/config';
        $filename = $configDir . '/limite_place_session.txt';
        $limite = file_get_contents($filename);
        return $limite;
    }    

    private function getMaxPlaceRDV():int
    {
        $configDir = $this->getParameter('kernel.project_dir') . '/config';
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

    private function getNumEtud(int $num_etud_compare):bool
    {
        $publicDir = $this->getParameter('kernel.project_dir') . '/public';
        $filename = $publicDir . '/donnee/num_etud.json';
        $num_etud = json_decode(file_get_contents($filename), true);
        foreach ($num_etud as $num) {
            if ($num_etud_compare == $num) {
                return true;
            }
        }
        return false;
    }

    #[Route('/inscription', name: 'inscription')]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_ELEVE']);
            $user->setType(2);

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
            if (!$num_etud_exist) {
                $this->addFlash('danger', "Le numéro étudiant n'existe pas");
                return $this->redirectToRoute('inscription');
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
            
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', "Votre compte a bien été créé, vous pouvez vous connecter");
            return $this->redirectToRoute('connexion');
        }
        return $this->render('eleve/inscription_eleve.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    
    #[Route ("/presentation/eleve/{nom}/{prenom}/{id}", name: "front_eleve")]
    public function presentationEleve(String $nom, String $prenom, int $id, UserRepository $urp): Response
    {   
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('connexion');
        }
        $eleve = $urp->findOneBy(['nom' => $nom, 'prenom' => $prenom, "type" => 2, "id" => $id]);
        return $this->render('eleve/presentation_eleve.html.twig', [
            'eleve' => $eleve
        ]);
    }

    #[Route ("/liste/eleve/{page<\d+>?1}", name: "front_liste_eleve")]
    public function listeEleve(UserRepository $urp, PaginatorInterface $paginator,int $page, Request $request): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour accéder à cette page');
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
        else if ($user->getType() != 2) {
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
}
