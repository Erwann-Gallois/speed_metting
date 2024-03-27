<?php

namespace App\Controller;

use App\Form\UserChangeMPDType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        private ManagerRegistry $doctrine, 
        private Security $security, 
    )
    {
    }
    #[Route(path: '/connexion', name: 'connexion')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/deconnexion', name: 'deconnexion')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/mdp-oublie', name: 'mdp_oublie')]
    public function mdpOublie(UserRepository $urp, MailerInterface $mailer): Response
    {
        $formbuilder = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Email'
                ],
                'required' => true
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => [
                    'class' => 'btn btn-primary'
                ],
                'required' => true
            ]);
        $form = $formbuilder->getForm();
        if ($form->isSubmitted() && $form->isValid()){
            $data = $form->getData();
            $user = $urp->findOneBy(['email' => $data['email']]);
            if ($user){
                $token = md5(uniqid());
                $user->setToken($token);
                $em = $this->doctrine->getManager();
                $em->persist($user);
                $em->flush();
                $message = (new TemplatedEmail())
                ->from(new Address("no.reply.speed.meetings2024@univ-evry.fr", "Speed Meetings 2024"))
                ->to($data["email"])
                ->subject("Réinitialisation de votre mot de passe")
                // path of the Twig template to render
                ->htmlTemplate('mail/mdp_oublie.html.twig')
                // pass variables (name => value) to the template
                ->context([
                    "user" => $user,
                    "token" => $token
                ]);
                $mailer->send($message);
            }
        }
        return $this->render('security/mdp_oublie.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route(path: '/reinitialisation-mdp/{token}', name: 'reinitialisation_mdp')]
    public function reinitialisationMdp(String $token, UserRepository $urp, UserPasswordHasherInterface $hasher): Response
    {
        $user = $urp->findOneBy(['token' => $token]);
        if ($user){
            $formbuilder = $this->createFormBuilder()
                ->add('nouveau_mdp', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Les mots de passe ne correspondent pas',
                    'options' => ['attr' => ['class' => 'password-field']],
                    'required' => true,
                    'first_options' => ['label' => 'Nouveau mot de passe'],
                    'second_options' => ['label' => 'Répéter le mot de passe']
                ])
                ->add('submit', SubmitType::class, [
                    'label' => 'Envoyer',
                    'attr' => [
                        'class' => 'btn btn-primary'
                    ],
                    'required' => true
                ]);
            $form = $formbuilder->getForm();
            if ($form->isSubmitted() && $form->isValid()){
                $data = $form->getData();
                $user->setPassword($hasher->hashPassword($user, $data['nouveau_mdp']));
                $user->setToken('null');
                $em = $this->doctrine->getManager();
                $em->persist($user);
                $em->flush();
                return $this->redirectToRoute('connexion');
            }
            return $this->render('security/reinitialisation_mdp.html.twig', [
                'form' => $form->createView()
            ]);
        }
        else {
            $this->addFlash('danger', 'Le lien est invalide');
            return $this->redirectToRoute('accueil');
        }
    }

    #[Route('/compte/pro/changer_mdp', name: 'pro_changer_mdp')]
    #[Route('/compte/eleve/changer_mdp', name: 'eleve_changer_mdp')]
    public function editPassword(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $eM): Response
    {
        $user = $this->security->getUser();
        if (!$user) {
            return $this->redirectToRoute('connexion');
        }
        $form = $this->createForm(UserChangeMPDType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $form->get('ancien_mdp')->getData();
            if ($hasher->isPasswordValid($user, $oldPassword)) {
                if ($form->get('plainPassword1')->getData() == $form->get('plainPassword2')->getData()) {
                    $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword1')->getData()));
                    $eM->persist($user);
                    $eM->flush();
                    $this->addFlash('success', 'Mot de passe modifié avec succès');
                    if ($user->getType() == 1) {
                        return $this->redirectToRoute('compte_pro');
                    }
                    else {
                        return $this->redirectToRoute('compte_eleve');
                    }
                }
                else {
                    $this->addFlash('danger', 'Les mots de passe ne correspondent pas');
                    if ($user->getType() == 1) {
                        return $this->redirectToRoute('pro_changer_mdp');
                    }
                    else {
                        return $this->redirectToRoute('eleve_changer_mdp');
                    }
                }
            }
            else {
                $this->addFlash('danger', 'Ancien mot de passe incorrect');
                if ($user->getType() == 1) {
                    return $this->redirectToRoute('pro_changer_mdp');
                }
                else {
                    return $this->redirectToRoute('eleve_changer_mdp');
                }
            }
        }
        return $this->render('security/edit_Password.html.twig',[
            'form'=> $form->createView(),
            'user' => $user
        ]);
    }
    
}
