<?php

namespace App\Controller;

use App\Form\RegistrationProFormType;
use App\Form\UserProType;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfessionnelController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $eM,
        private Security $security,
        private ManagerRegistry $doctrine
    )
    {
    }

    #[Route("/presentation/professionnel/{nom}/{prenom}/{id}", name: "front_pro")]
    public function presentationProfessionnel(String $nom, String $prenom, int $id, UserRepository $urp, TranslatorInterface $transaltor): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('warning', $transaltor->trans("flash.no_connect"));
            return $this->redirectToRoute('connexion');
        }
        $pro = $urp->findOneBy(['nom' => $nom, 'prenom' => $prenom, "type" => 1, "id" => $id]);
        if ($pro === null) {
            throw $this->createNotFoundException($transaltor->trans("flash.pro_not_found"));
        }
        return $this->render('professionnel/presentation_professionnel.html.twig', [
            'pro' => $pro
        ]);
    }  

    
    #[Route ("/liste/professionnel", name: "liste_pro")]
    public function listePro(UserRepository $urp, TranslatorInterface $transaltor): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            $this->addFlash('warning', $transaltor->trans("flash.no_connect"));
            return $this->redirectToRoute('connexion');
        }
        $pros = $urp->findBy(['type' => 1, "info_valid" => 1]);
        return $this->render('professionnel/liste_pro.html.twig', [
            'pros' => $pros
        ]);
    }

    #[Route ("inscription/pro/{token}", name : "inscription_pro")]
    public function inscriptionPro (String $token, Request $request,  UserRepository $urp, UserPasswordHasherInterface $hasher, TranslatorInterface $transaltor)
    {
        $user = $urp->findOneBy(['token' => $token]);
        $form = $this->createForm(RegistrationProFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setInfoValid(true);
            $user->setToken("");
            if ($form->get('plainPassword1')->getData() != $form->get('plainPassword2')->getData()) {
                $this->addFlash('danger', $transaltor->trans("flash.mdp_dif"));
                return $this->redirectToRoute('inscription');
            }
            $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword1')->getData()));
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
            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', $transaltor->trans('flash_compte_pro_creer'));
            return $this->redirectToRoute('connexion');
        }
        return $this->render('professionnel/inscription_pro.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/compte/pro', name: 'compte_pro')]
    #[IsGranted('ROLE_PROFESSIONNEL')]
    public function comptePro(): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('connexion');
        }
        elseif ($user->getType() != 1) {
            return $this->redirectToRoute('compte');
        }
        return $this->render('professionnel/compte_pro.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/compte/pro/modifier', name: 'pro_modifier')]
    #[IsGranted('ROLE_PROFESSIONNEL')]
    public function editPro(Request $request, EntityManagerInterface $eM, TranslatorInterface $transaltor): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('connexion');
        }
        else if ($user->getType() != 1) {
            return $this->redirectToRoute('compte');
        }
        $form = $this->createForm(UserProType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
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
            $this->addFlash('success', $transaltor->trans("flash.modf_profil"));
            return $this->redirectToRoute('compte');
        }
        return $this->render('professionnel/edit_pro.html.twig',[
            'form'=> $form->createView(),
            'user' => $user
        ]);
    }
    
    #[Route("/compte/pro/planning/{nom}/{prenom}/{id}", name: "planning_pro")]
    #[IsGranted('ROLE_PROFESSIONNEL')]
    public function planningPro(String $nom, String $prenom, int $id, SessionRepository $srp, UserRepository $urp):Response
    {
        $user = $urp->findOneBy(['id' => $id, 'nom' => $nom, 'prenom' => $prenom, 'type' => 1]);
        if (!$user) {
            return $this->redirectToRoute('connexion');
        }
        else if ($user->getType() != 1) {
            return $this->redirectToRoute('planning');
        }
        $sessions = $srp->findAllSessionPro($user->getId());
        $all_sessions_by_hour = [];
        foreach ($sessions as $session) {
            $date = $session->getHeure();
            $all_sessions_by_hour[$date->format('H:i')][] = $session;
        }
        // dump($all_sessions_by_hour);
        return $this->render('professionnel/planning_pro.html.twig', [
            'user' => $user,
            'sessions' => $all_sessions_by_hour
        ]);
    }

    #[Route("/supprimer/{nom}/{prenom}/{id}", name: "supprimer_pro_front")]
    public function supprimerPro(int $id, String $nom, String $prenom, UserRepository $urp, Request $request, TokenStorageInterface $tokenStorage, TranslatorInterface $translator): Response
    {
        $em = $this->doctrine->getManager();
        $pro = $urp->findOneBy(['id' => $id, 'type' => 1, 'nom' => $nom, 'prenom' => $prenom]);
        if (!$pro) {
            $this->addFlash("error", $translator->trans("flash.pro_not_found"));
            return $this->redirectToRoute("accueil");
        }
        // Avant la suppression, déconnectez l'utilisateur si c'est l'utilisateur actuellement connecté
        if ($this->getUser() && $this->getUser()->getId() === $pro->getId()) {
            // Déconnexion de l'utilisateur
            $tokenStorage->setToken(null);
            $request->getSession()->invalidate();
        }
        $name = $pro->getImageName();
        if ($name != "personne_lambda.png" && $name != null) {
            unlink($this->getParameter('kernel.project_dir').'/public/image_profil/'.$name);
        }
        for ($i = 0; $i < count($pro->getSessions()); $i++) {
            $session = $pro->getSessions()[$i];
            $em->remove($session);
        }
        $em->remove($pro);
        $em->flush();
        $this->addFlash("success", $translator->trans("flash.supp_compte"));
        return $this->redirectToRoute("accueil");
    }
}
