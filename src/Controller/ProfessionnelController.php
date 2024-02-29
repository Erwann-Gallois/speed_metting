<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ELEVE')]
#[IsGranted('ROLE_PROFESSIONNEL')]
class ProfessionnelController extends AbstractController
{
    #[Route("/presentation/professionnel/{nom}/{prenom}/{id}", name: "front_pro")]
    public function presentationProfessionnel(String $nom, String $prenom, int $id, UserRepository $urp): Response
    {
        $pro = $urp->findOneBy(['nom' => $nom, 'prenom' => $prenom, "type" => 1, "id" => $id]);
        if ($pro === null) {
            throw $this->createNotFoundException('Professionnel non trouvé');
        }
        return $this->render('professionnel/presentation_professionnel.html.twig', [
            'pro' => $pro
        ]);
    }  
}
