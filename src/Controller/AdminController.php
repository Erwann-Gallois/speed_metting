<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmailSelectionType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
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
    public function listeProfessionnel(Request $request): Response
    {
        $pros = $this->doctrine->getRepository(User::class)->findBy(["type" => 1]);
        // dump($pro);
        $emails = array_map(function ($pro) {
            return $pro->getEmail();
        }, $pros);
        $form = $this->createForm(EmailSelectionType::class, null, ['emails' => $emails]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $selectedEmails = [];
            foreach ($emails as $index => $email) {
                $checkboxField = 'email_' . $index;
                if ($form->has($checkboxField) && $form->get($checkboxField)->getData() === true) {
                    $selectedEmails[] = $email;
                }
            }
            dump($selectedEmails); // Cela devrait maintenant vous montrer les e-mails sélectionnés
        }
        return $this->render('admin/liste_professionnel.html.twig', [
            'pros' => $pros,
            'form' => $form->createView(),
            // 'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/liste/eleve', name: 'liste_eleve')]
    public function listeEleve(Request $request): Response
    {
        $pros = $this->doctrine->getRepository(User::class)->findBy(["type" => 2]);
        // dump($pro);
        $emails = array_map(function ($pro) {
            return $pro->getEmail();
        }, $pros);
        $form = $this->createForm(EmailSelectionType::class, null, ['emails' => $emails]);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $selectedEmails = [];
            foreach ($emails as $index => $email) {
                $checkboxField = 'email_' . $index;
                if ($form->has($checkboxField) && $form->get($checkboxField)->getData() === true) {
                    $selectedEmails[] = $email;
                }
            }
            dump($selectedEmails); // Cela devrait maintenant vous montrer les e-mails sélectionnés
        }
        return $this->render('admin/liste_eleve.html.twig', [
            'pros' => $pros,
            'form' => $form->createView(),
            // 'controller_name' => 'AdminController',
        ]);
    }
}
