<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Spipu\Html2Pdf\Html2Pdf;

class PDFController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $doctrine, 
        private Security $security, 
    )
    {
    }
    #[Route('/impression/fiche/professionnel/{nom}/{prenom}/{id}' , name: 'impression_fiche_professionnel')]
    public function impressionFicheProfessionnel(String $nom, String $prenom, int $id)
    {
        $user = $this->doctrine->getRepository(User::class)->findBy(["id"=>$id, "type"=>1, "nom"=>$nom, "prenom"=>$prenom])[0];
        $html2pdf = new Html2Pdf('P', 'A4', 'fr', true, 'UTF-8', array(10, 15, 10, 15));
        $html = $this->renderView('pdf/fiche_professionnel.html.twig', [
            'user' => $user
        ]);
        $html2pdf->writeHTML($html);
        $html2pdf->output($user->getNom().'_'.$user->getPrenom().'_'.'fiche_professionnel.pdf');  
    }

    #[Route('/impression/fiche/eleve/{nom}/{prenom}/{id}' , name: 'impression_fiche_eleve')]
    public function impressionFicheEleve(String $nom, String $prenom, int $id)
    {
        $user = $this->doctrine->getRepository(User::class)->findBy(["id"=>$id, "type"=>2, "nom"=>$nom, "prenom"=>$prenom])[0];
        $html2pdf = new Html2Pdf('P', 'A4', 'fr', true, 'UTF-8', array(10, 15, 10, 15));
        $html = $this->renderView('pdf/fiche_eleve.html.twig', [
            'user' => $user
        ]);
        $html2pdf->writeHTML($html);
        $html2pdf->output($user->getNom().'_'.$user->getPrenom().'_'.'fiche_eleve.pdf');  
    }
}
