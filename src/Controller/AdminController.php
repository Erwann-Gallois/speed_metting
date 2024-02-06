<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmailSelectionType;
use App\Form\LimitePlacesFormType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Filesystem\Filesystem;
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
        $eleves = $this->doctrine->getRepository(User::class)->findBy(["type" => 2]);
        // dump($eleve);
        $emails = array_map(function ($eleve) {
            return $eleve->getEmail();
        }, $eleves);
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
            'eleves' => $eleves,
            'form' => $form->createView(),
            // 'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/config/limite-places', name: 'config_limite_places')]
    public function updateLimitePlaces(Request $request): Response
    {
        $form = $this->createForm(LimitePlacesFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $limitePlaces = $form->get('limite_places')->getData();

            // Utiliser le Filesystem pour écrire dans un fichier de config ou une autre méthode de stockage
            $filesystem = new Filesystem();
            $filesystem->dumpFile($this->getParameter('config_directory') . '/limite_places.txt', $limitePlaces);

            // Gérer la réponse AJAX ou rediriger normalement si non-AJAX
            if ($request->isXmlHttpRequest()) {
                return new Response('La limite de places a été mise à jour.');
            } else {
                $this->addFlash('success', 'La limite de places a été mise à jour.');
                return $this->redirectToRoute('accueil');
            }
        }

        return $this->render('configuration/limitePlaces.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
