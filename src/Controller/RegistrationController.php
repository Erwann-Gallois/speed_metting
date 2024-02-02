<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\QuestionEleveType;
use App\Form\QuestionProType;
use App\Form\RegistrationFormType;
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

    #[Route('/inscription', name: 'inscription')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $type = $form->get('type')->getData();
            switch ($type) {
                case 1:
                    $user->setRoles(['ROLE_PROFESSIONNEL']);
                    $entityManager->persist($user);
                    $entityManager->flush();
                    return $this->redirectToRoute("inscription-step2", ['nom' => $user->getNom(), 'prenom' => $user->getPrenom()]);
                    break;
                case 2:
                    $user->setRoles(['ROLE_ELEVE']);
                    $entityManager->persist($user);
                    $entityManager->flush();
                    return $this->redirectToRoute('inscription-step2', ['nom' => $user->getNom(), 'prenom' => $user->getPrenom()]);
                    break;
                case 3:
                    $user->setRoles(['ROLE_ORGANISATEUR']);
                    $entityManager->persist($user);
                    $entityManager->flush();
                    return $this->redirectToRoute('connexion');
                    break;
            }
            
            // return $this->redirectToRoute('connexion');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/inscription/{nom}-{prenom}/question', name: 'inscription-step2')]
    public function inscription_step2 (String $nom, String $prenom, Request $request, EntityManagerInterface $entityManager)
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nom' => $nom, 'prenom' => $prenom]);
        if (!$user) {
            return $this->redirectToRoute('inscription');
        }
        if ($user->getType() == 1)
        {
            $form = $this->createForm(QuestionProType::class);
        }
        else
        {
            $form = $this->createForm(QuestionEleveType::class);
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setQuestion($form->get('question')->getData());
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('connexion');
        }
        return $this->render('registration/inscription-step2.html.twig', [
            'questionForm' => $form->createView(),
            'user' => $user
        ]);
    }
}
