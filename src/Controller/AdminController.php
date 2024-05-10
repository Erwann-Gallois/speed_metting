<?php

namespace App\Controller;

use App\Entity\NumeroEtudiant;
use App\Entity\User;
use App\Entity\Variable;
use App\Form\NumeroEtudiantType;
use App\Form\ProfessionnelType;
use App\Form\VariableType;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use DateTime;
use DateTimeZone;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
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
        $em = $this->doctrine->getManager();
        $variable = $em->getRepository(Variable::class)->findBy(["id" => 1]);
        $form = $this->createForm(VariableType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();
            $em = $this->doctrine->getManager();
            $variable = $em->getRepository(Variable::class)->findBy(["id" => 1]);
            if (count($variable) === 0)
            {
                $variable = new Variable();
                $variable->setDateFinInscription($data->getDateFinInscription());
                $variable->setDateOuverResa($data->getDateOuverResa());
                $variable->setDateFinResa($data->getDateFinResa());
                $variable->setPlaceSession($data->getPlaceSession());
                $variable->setPlaceRdv($data->getPlaceRdv());
                $em->persist($variable);
                $em->flush();
                $this->addFlash("success", "Les variables ont été modifiées");
                return $this->redirectToRoute("admin");
            }
            $variable = $variable[0];
            $date_fin_inscription = ($data["date_fin_inscription"] === null) ? $variable->getDateFinInscription() : $data["date_fin_inscription"];
            $date_ouver_resa = ($data["date_ouver_resa"] === null) ? $variable->getDateOuverResa() : $data["date_ouver_resa"];
            $date_fin_resa = ($data["date_fin_resa"] === null) ? $variable->getDateFinResa() : $data["date_fin_resa"];
            $place_session = ($data["place_session"] === null) ? $variable->getPlaceSession() : $data["place_session"];
            $place_rdv = ($data["place_rdv"] === null) ? $variable->getPlaceRdv() : $data["place_rdv"];
            $variable->setDateFinInscription($date_fin_inscription);
            $variable->setDateOuverResa($date_ouver_resa);
            $variable->setDateFinResa($date_fin_resa);
            $variable->setPlaceSession($place_session);
            $variable->setPlaceRdv($place_rdv);
            $em->persist($variable);
            $em->flush();
            $this->addFlash("success", "Les variables ont été modifiées");
            return $this->redirectToRoute("admin");
        }
        if ($variable === [])
        {
            return $this->render('admin/index.html.twig', [
                'form' => $form->createView(),
                'nbre_eleve' => $nbre_eleve,
                'nbre_orga' => $nbre_organisateur,
                'nbre_pro' => $nbre_pro,
                'variable' => null
            ]);
        }
        else
        {
            $variable = $variable[0];
            $date_fin_inscription = $variable->getDateFinInscription()->format("d/m/Y H:i");
            $date_ouver_resa = $variable->getDateOuverResa()->format("d/m/Y H:i");
            $date_fin_resa = $variable->getDateFinResa()->format("d/m/Y H:i");
            $place_session = $variable->getPlaceSession();
            $place_rdv = $variable->getPlaceRdv();
            return $this->render('admin/index.html.twig', [
                'form' => $form->createView(),
                'nbre_eleve' => $nbre_eleve,
                'nbre_orga' => $nbre_organisateur,
                'nbre_pro' => $nbre_pro,
                'variable' => $variable,
                'date_fin_inscription' => $date_fin_inscription,
                'date_ouver_resa' => $date_ouver_resa,
                'date_fin_resa' => $date_fin_resa,
                'place_session' => $place_session,
                'place_rdv' => $place_rdv
            ]);
        }
    }

    #[Route('/ajouter/numero_etudiant', name: 'add_num_etud')]
    public function addNumEtud(Request $request): Response
    {
        $em = $this->doctrine->getManager();
        $num_etud = new NumeroEtudiant();
        $form = $this->createForm(NumeroEtudiantType::class, $num_etud);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $verif = $em->getRepository(NumeroEtudiant::class)->findBy(["numero" => $num_etud->getNumero()]);
            if (count($verif) > 0)
            {
                $this->addFlash("danger", "Ce numéro étudiant existe déjà");
                return $this->redirectToRoute("admin");
            }
            $em->persist($num_etud);
            $em->flush();
            $this->addFlash("success", "Le numéro étudiant a été ajouté");
            return $this->redirectToRoute("admin");
        }
        return $this->render('admin/add_num_etud.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/liste/professionnel', name: 'liste_professionnel')]
    public function listeProfessionnel(UserRepository $urp): Response
    {
        $pros = $urp->findBy(["type" => 1], ["nom" => "ASC"]);
        return $this->render('admin/liste_professionnel.html.twig', [
            'pros' => $pros,
        ]);
    }

    #[Route('/liste/eleve', name: 'liste_eleve')]
    public function listeEleve(SessionRepository $srp, UserRepository $urp): Response
    {
        $eleves = $urp->findBy(["type" => 2], ["nom" => "ASC"]);
        $nbre_session = [];
        foreach ($eleves as $key => $value) {
            $nbre_session[$key] = count($srp->findAllSessionEleve($value->getId()));
        }
        return $this->render('admin/liste_eleve.html.twig', [
            'eleves' => $eleves,
            'nbre_session' => $nbre_session,
        ]);
    }

    #[Route('/liste/eleve/session/{num_session}', name: 'liste_eleve_session')]
    public function listeEleveSession(int $num_session, UserRepository $urp, SessionRepository $srp): Response
    {
        $sessions = $urp->findAllUserBySession($num_session);
        $nbre_session = [];
        $nbre_eleve = count($sessions);
        foreach ($sessions as $key => $value) {
            $nbre_session[$key] = count($srp->findAllSessionEleve($value->getId()));
        }
        return $this->render('admin/liste_eleve_session.html.twig', [
            'eleves' => $sessions,
            'nbre_session' => $nbre_session,
            "nbre_eleve_session" => $nbre_eleve
        ]);
    }

    #[Route('/liste/organisateur', name: 'liste_organisateur')]
    public function listeorganisateur(UserRepository $urp): Response
    {
        $organisateurs = $urp->findBy(["type" => 3], ["nom" => "ASC"]);
        return $this->render('admin/liste_organisateur.html.twig', [
            'organisateurs' => $organisateurs,
        ]);
    }

    #[Route ("creation/professionnel", name : "creer_pro")]
    public function creer_pro (Request $request, UserPasswordHasherInterface $hasher, MailerInterface $mailer, UserRepository $urp)
    {
        $link = null;
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
            $user->setEntreprise($data["entreprise"]);
            $user->setRoles(["ROLE_PROFESSIONNEL"]);
            $user->setType(1);
            $user->setInfoValid(false);
            $user->setPoste($data["poste"]);
            $password = $hasher->hashPassword($user, uniqid('', true)); 
            $token = uniqid('', true);
            $user->setPassword($password);
            $user->setToken($token);
            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();
            $link = $token;
        }
        return $this->render("admin/creer_pro.html.twig", [
            "form" => $form->createView(),
            "link" => $link 
        ]);
    }   

    #[Route("/recuperer_lien/{id}", name: "recuperer_lien")]
    public function recupererLienPro(int $id, UserRepository $urp): Response
    {
        $user = $urp->find($id);
        if ($user === null) {
            $this->addFlash("danger", "L'utilisateur n'existe pas");
        }
        else if ($user->getToken() === "" && $user->isInfoValid() === true) {
            $this->addFlash("danger", "L'utilisateur a déjà rempli le formulaire");
        }
        else if ($user->getToken() === "" && $user->isInfoValid() === false) {
            $this->addFlash("danger", "L'utilisateur a déjà rempli le formulaire mais il n'a pas été validé");
        }
        else if($user->getToken() !== "") {
            $link = $this->generateUrl("inscription_pro", ["token" => $user->getToken()], UrlGenerator::ABSOLUTE_URL);
            $this->addFlash("info", "Le lien de connexion est : \n" . $link);
        }
        return $this->redirectToRoute("liste_professionnel");
    }


    #[Route("/supprimer/{id}", name: "supprimer")]
    public function supprimerPro(int $id, UserRepository $urp): Response
    {
        $em = $this->doctrine->getManager();
        $pro = $urp->find($id);
        for ($i = 0; $i < count($pro->getSessions()); $i++) {
            $session = $pro->getSessions()[$i];
            $em->remove($session);
        }
        $em->remove($pro);
        $em->flush();
        $this->addFlash("success", "L'utilisateur a été supprimé");
        return $this->redirectToRoute("admin");
    }

}
