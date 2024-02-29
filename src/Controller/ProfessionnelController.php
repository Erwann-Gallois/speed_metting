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

    
    #[Route ("/liste/professionnel", name: "liste_pro")]
    public function listePro(UserRepository $urp): Response
    {
        $pros = $urp->findBy(['type' => 1]);
        return $this->render('professionnel/liste_pro.html.twig', [
            'pros' => $pros
        ]);
    }
}
