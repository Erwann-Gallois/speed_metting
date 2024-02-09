<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;

class PDFController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $doctrine, 
        private Security $security, 
    )
    {
    }
    #[Route('/impression/fiche/professionnel/{nom}/{prenom}/{id}' , name: 'impression_fiche_professionnel')]
    public function impressionFicheProfessionnel(String $nom, String $prenom, int $id, Pdf $knpSnappyPdf): Response
    {
        $user = $this->doctrine->getRepository(User::class)->findBy(["id"=>$id, "type"=>1, "nom"=>$nom, "prenom"=>$prenom])[0];
        $html = $this->renderView('pdf/fiche_professionnel.html.twig', [
            'user' => $user,
        ]);
        $filename = $user->getNom()."_".$user->getPrenom()."_fiche_professionnel.pdf";
        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"'
            ]
        );
    }
}
