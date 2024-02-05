<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
// use Symfony\Component\CssSelector\XPath\TranslatorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/liste/professionnel', name: 'liste_professionnel')]
    public function listeProfessionnel(): Response
    {
        $pro = $this->doctrine->getRepository(User::class)->findBy(['roles' => 'ROLE_PROFESSIONNEL', "type" => 1]);
        return $this->render('admin/liste_professionnel.html.twig', [
            'pros' => $pro,
            // 'controller_name' => 'AdminController',
        ]);
    }
}
