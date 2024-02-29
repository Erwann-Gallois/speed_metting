<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\QuestionEleveType;
use App\Form\QuestionProType;
use App\Form\RegistrationFormType;
use App\Form\RegistrationProFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Boolean;
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

    private function getNumEtud(int $num_etud_compare):bool
    {
        $publicDir = $this->getParameter('kernel.project_dir') . '/public';
        $filename = $publicDir . '/donnee/num_etud.json';
        $num_etud = json_decode(file_get_contents($filename), true);
        foreach ($num_etud as $num) {
            if ($num_etud_compare == $num) {
                return true;
            }
        }
        return false;
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
            if ($form->get('plainPassword1')->getData() != $form->get('plainPassword2')->getData()) {
                $this->addFlash('danger', "Les mots de passe ne correspondent pas");
                return $this->redirectToRoute('inscription');
            }
            $num_etud_exist = $this->getNumEtud($form->get('numetud')->getData());
            if (!$num_etud_exist) {
                $this->addFlash('danger', "Le numéro étudiant n'existe pas");
                return $this->redirectToRoute('inscription');
            }
            $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword1')->getData()));
            if ($form->get('imageFile')->getData() != null) {
                $file = $form->get('imageFile')->getData();
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $file->move($this->getParameter('kernel.project_dir').'/public/image_profil', $fileName);
                $size = filesize($this->getParameter('kernel.project_dir').'/public/image_profil/'.$fileName);
                $user->setImageName($fileName);
                $user->setImageSize($size);
            }
            else {
                $user->setImageName('personne_lambda.png');
                $size = filesize($this->getParameter('kernel.project_dir').'/public/image_profil/personne_lambda.png');
                $user->setImageSize($size);
            }
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', "Votre compte a bien été créé, vous pouvez vous connecter");
            return $this->redirectToRoute('connexion');
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route ("inscription/pro/{token}", name : "inscription_pro")]
    public function inscriptionPro (String $token, Request $request,  UserRepository $urp, UserPasswordHasherInterface $hasher)
    {
        $user = $urp->findOneBy(['token' => $token]);
        $form = $this->createForm(RegistrationProFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setInfoValid(true);
            $user->setToken("");
            if ($form->get('plainPassword1')->getData() != $form->get('plainPassword2')->getData()) {
                $this->addFlash('danger', "Les mots de passe ne correspondent pas");
                return $this->redirectToRoute('inscription');
            }
            $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword1')->getData()));
            if ($form->get('imageFile')->getData() != null) {
                $file = $form->get('imageFile')->getData();
                $fileName = md5(uniqid()).'.'.$file->guessExtension();
                $file->move($this->getParameter('kernel.project_dir').'/public/image_profil', $fileName);
                $size = filesize($this->getParameter('kernel.project_dir').'/public/image_profil/'.$fileName);
                $user->setImageName($fileName);
                $user->setImageSize($size);
            }
            else {
                $user->setImageName('personne_lambda.png');
                $size = filesize($this->getParameter('kernel.project_dir').'/public/image_profil/personne_lambda.png');
                $user->setImageSize($size);
            }
            $em = $this->doctrine->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Votre compte a bien été créé, vous pouvez vous connecter');
            return $this->redirectToRoute('connexion');
        }

        return $this->render('registration/inscription_pro.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
