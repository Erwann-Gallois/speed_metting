<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\QuestionEleveType;
use App\Form\QuestionProType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $doctrine, 
        private Security $security, 
        private TranslatorInterface $translator
        )
    {
    }

    private function getMaxPlaceSession():int
    {
        $configDir = $this->getParameter('kernel.project_dir') . '/config';
        $filename = $configDir . '/limite_place_session.txt';
        $limite = file_get_contents($filename);
        return $limite;
    }    

    #[Route('/inscription', name: 'inscription')]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(['ROLE_ELEVE']);
            $user->setType(2);
            $user->setPassword(
                $hasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', "Votre compte a bien été créé, vous pouvez vous connecter");
            return $this->redirectToRoute('connexion');
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
