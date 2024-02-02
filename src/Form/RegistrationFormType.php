<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['autocomplete' => 'email'],
                'label' => 'Email (*)',
                'required' => true,
            ])
            ->add('nom', TextType::class, [
                'attr' => ['autocomplete' => 'nom'],
                'label' => 'Nom (*)',
                'required' => true,
            ])
            ->add('prenom', TextType::class, [
                'attr' => ['autocomplete' => 'prenom'],
                'label' => 'Prénom (*)',
                'required' => true,
            ])
            ->add('type', ChoiceType::class, [
                'required' => true,
                'choices' => [
                    'Professionnel' => 1,
                    'Eleve' => 2,
                    'Organisateur' => 3,
                ],
                'label' => 'Je suis (*)',
                'expanded' => false,

            ])
            ->add('plainPassword', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'invalid_message' => 'Les mots de passe ne correspondent pas',
                'options' => ['attr' => ['class' => 'password']],
                'required' => true,
                'first_options'  => ['label' => 'Mot de Passe (*)'],
                'second_options' => ['label' => 'Répéter le mot de passe (*)'],
                'type'=>PasswordType::class,
                'attr' => [
                    'autocomplete' => 'new-password',
                    'class'=>'form-control'
            ],
            'label'=>'Mot de passe',

                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
