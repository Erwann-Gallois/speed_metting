<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
            return $this->redirectToRoute('app_login');
        }
        else if ($user->getType() == 1) {
            return $this->redirectToRoute('compte_pro');
        }
        else if ($user->getType() == 2) {
            return $this->redirectToRoute('compte_eleve');
        }
    }

    #[Route('/compte/pro', name: 'compte_pro')]
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
    public function compteEleve(): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('connexion');
        }
        elseif ($user->getType() != 2) {
            return $this->redirectToRoute('compte');
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
            return $this->redirectToRoute('app_login');
        }
        else if ($user->getType() == 1) {
            return $this->redirectToRoute('planning_pro');
        }
        else if ($user->getType() == 2) {
            return $this->redirectToRoute('planning_eleve');
        }
    }

    #[Route("/compte/pro/planning", name: "planning_pro")]
    public function planningPro():Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        else if ($user->getType() != 1) {
            return $this->redirectToRoute('planning');
        }
        return $this->render('user/planning_pro.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user
        ]);
    }

    #[Route("/compte/eleve/planning", name: "planning_eleve")]
    public function planningEleve():Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        else if ($user->getType() != 2) {
            return $this->redirectToRoute('planning');
        }
        return $this->render('user/planning_eleve.html.twig', [
            'controller_name' => 'UserController',
            'user' => $user
        ]);
    }
}
