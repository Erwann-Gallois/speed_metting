<?php

namespace App\Controller;

use App\Entity\Session;
use App\Form\UserChangeMPDType;
use App\Form\UserEleveType;
use App\Form\UserProType;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $doctrine, 
        private Security $security, 
    )
    {
    }
    #[Route('/compte', name: 'compte')]
    public function index(): Response
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
        return $this->render('user/compte_pro.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user
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
        return $this->render('user/compte_eleve.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user
        ]);
    }

    #[Route("/compte/planning", name: "planning")]
    public function planning():Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('connexion');
        }
        else if ($user->getType() == 1) {
            return $this->redirectToRoute('planning_pro');
        }
        else if ($user->getType() == 2 || $user->getType() == 3){
            return $this->redirectToRoute('planning_eleve');
        }
    }

    #[Route("/compte/pro/planning", name: "planning_pro")]
    #[IsGranted('ROLE_PROFESSIONNEL')]
    public function planningPro(SessionRepository $srp):Response
    {
        $user = $this->security->getUser();
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
        return $this->render('user/planning_pro.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user,
            'sessions' => $all_sessions_by_hour
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
        return $this->render('user/planning_eleve.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user,
            'sessions' => $sessions
        ]);
    }



    #[Route('/compte/pro/changer_mdp', name: 'pro_changer_mdp')]
    #[Route('/compte/eleve/changer_mdp', name: 'eleve_changer_mdp')]
    public function editPassword( Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $eM): Response
    {
        $user = $this->security->getUser();
        $form = $this->createForm(UserChangeMPDType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $form->get('ancien_mdp')->getData();
            if ($hasher->isPasswordValid($user, $oldPassword)) {
                $newPassword = $form->get('nouveau_mdp')->getData();
                $user->setPassword($hasher->hashPassword($user, $newPassword));
                $eM->flush();
                $this->addFlash('success', 'Mot de passe modifié avec succès');
                return $this->redirectToRoute('compte');
            }
            else {
                $this->addFlash('danger', 'Ancien mot de passe incorrect');
                if ($user->getType() == 1) {
                    return $this->redirectToRoute('pro_changer_mdp');
                }
                else {
                    return $this->redirectToRoute('eleve_changer_mdp');
                }
            }
        }
        return $this->render('user/edit_Password.html.twig',[
            'form'=> $form->createView(),
            'user' => $user
        ]);
    }

    #[Route('/compte/pro/modifier', name: 'pro_modifier')]
    public function editPro(Request $request, EntityManagerInterface $eM): Response
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
        return $this->render('user/edit_pro.html.twig',[
            'form'=> $form->createView(),
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
                if ($name != "personne_lambda.jpg") {
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
        return $this->render('user/edit_eleve.html.twig',[
            'form'=> $form->createView(),
            'user' => $user
        ]);
    }
}
