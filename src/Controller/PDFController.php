<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\User;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Annotation\Route;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Contracts\Translation\TranslatorInterface;

class PDFController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $doctrine, 
        private Security $security, 
    )
    {
    }
    #[Route('/impression/fiche/professionnel/{nom}/{prenom}/{id}' , name: 'impression_fiche_professionnel')]
    public function impressionFicheProfessionnel(String $nom, String $prenom, int $id, UserRepository $urp)
    {
        $user = $urp->findBy(["id"=>$id, "type"=>1, "nom"=>$nom, "prenom"=>$prenom])[0];
        if ($user->getImageName() == null) {
            $imagePath = $this->getParameter('kernel.project_dir').'/public/images/personne_lambda.png';
        }
        else {
            $imagePath = $this->getParameter('kernel.project_dir').'/public/image_profil/';
            $imagePath .= $user->getImageName();
        }
        $html2pdf = new Html2Pdf('P', 'A4', 'fr', true, 'UTF-8', array(10, 15, 10, 15));
        $html = $this->renderView('pdf/fiche_professionnel.html.twig', [
            'user' => $user,
            'imagePath' => $imagePath
        ]);
        $html2pdf->writeHTML($html);
        $html2pdf->output($user->getNom().'_'.$user->getPrenom().'_'.'fiche_professionnel.pdf');  
    }

    #[Route('/impression/fiche/eleve/{nom}/{prenom}/{id}' , name: 'impression_fiche_eleve')]
    public function impressionFicheEleve(String $nom, String $prenom, int $id, UserRepository $urp, TranslatorInterface $translator)
    {
        $user = $urp->findBy(["id"=>$id, "type"=>2, "nom"=>$nom, "prenom"=>$prenom])[0];
        if ($user == null) {
            $user = $urp->findBy(["id"=>$id, "type"=>3, "nom"=>$nom, "prenom"=>$prenom])[0];
        }
        if ($user == null) {
            $this->addFlash('danger', $translator->trans('pdf.error'));
            return $this->redirectToRoute('accueil');
        }
        if ($user->getImageName() == null) {
            $imagePath = $this->getParameter('kernel.project_dir').'/public/images/personne_lambda.png';
        }
        else {
            $imagePath = $this->getParameter('kernel.project_dir').'/public/image_profil/';
            $imagePath .= $user->getImageName();
        }
        $html2pdf = new Html2Pdf('P', 'A4', 'fr', true, 'UTF-8', array(10, 15, 10, 15));
        $html = $this->renderView('pdf/fiche_eleve.html.twig', [
            'user' => $user,
            'imagePath' => $imagePath
        ]);
        $html2pdf->writeHTML($html);
        $html2pdf->output($user->getNom().'_'.$user->getPrenom().'_'.'fiche_eleve.pdf');  
    }

    #[Route('/impression/planning/{nom}/{prenom}/{id}' , name: 'impression_planning_eleve')]
    public function impressionPlanningEleve(String $nom, String $prenom, int $id, UserRepository $urp, SessionRepository $srp)
    {
        $user = $urp->findBy(["id"=>$id,"nom"=>$nom, "prenom"=>$prenom])[0];
        $sessions = $srp->findAllSessionEleve($user->getId());
        $html2pdf = new Html2Pdf('P', 'A4', 'fr', true, 'UTF-8', array(10, 15, 10, 15));
        $html = $this->renderView('pdf/planning_eleve.html.twig', [
            'user' => $user,
            'sessions' => $sessions
        ]);
        $html2pdf->writeHTML($html);
        $html2pdf->output($user->getNom().'_'.$user->getPrenom().'_'.'planning.pdf');  
    }

    #[Route('/impression/planning/professionnel/{nom}/{prenom}/{id}' , name: 'impression_planning_professionnel')]
    public function impressionPlanningProfessionnel(String $nom, String $prenom, int $id, UserRepository $urp, SessionRepository $srp)
    {
        $user = $urp->findBy(["id"=>$id, "type"=>1, "nom"=>$nom, "prenom"=>$prenom])[0];
        $sessions = $srp->findAllSessionPro($user->getId());
        $all_sessions_by_hour = [];
        foreach ($sessions as $session) {
            $date = $session->getHeure();
            $all_sessions_by_hour[$date->format('H:i')][] = $session;
        }
        $html2pdf = new Html2Pdf('L', 'A4', 'fr', true, 'UTF-8', array(10, 15, 10, 15));
        $html = $this->renderView('pdf/planning_professionnel.html.twig', [
            'user' => $user,
            'sessions' => $all_sessions_by_hour
        ]);
        $html2pdf->writeHTML($html);
        $html2pdf->output($user->getNom().'_'.$user->getPrenom().'_'.'planning.pdf');  
    }
}
