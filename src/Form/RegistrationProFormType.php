<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationProFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => "Nom (*)",
                "required" => true,
            ])
            ->add('prenom', TextType::class, [
                'label' => "Prénom (*)",
                "required" => true,
            ])
            ->add("email", EmailType::class, [
                "label" => "Email (*)",
                "required" => true
            ])
            ->add('entreprise', TextType::class, [
                "label" => "Nom de l'entreprise (*)",
                "required" => true,
            ])
            ->add('poste', TextType::class, [
                "label" => "Intitulé du poste (*)",
                "required"=> false
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent être identiques.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Mot de passe (*)'],
                'second_options' => ['label' => 'Répétez le mot de passe (*)'],
            ])
            ->add('question', TextareaType::class, [
                'label' => "Qu'est-ce qui vous passionne dans votre métier ? (*)",
                'attr' => [
                    'placeholder' => "Qu'est-ce qui vous passionne dans votre métier ?",
                    'class' => "form-control",
                    'rows' => 5,
                ],
                'required' => true,
            ])
            ->add("etude", TextareaType::class, [
                "label" => "Quel est votre parcours d'étude ? (*)",
                "attr" => [
                    "placeholder" => "Quel est votre parcours d'étude ?",
                    "class" => "form-control",
                    "rows" => 5,
                ],
                "required" => true,
            ])
            
            ->add("valid", CheckboxType::class, [
                "label" => "Je valide mes réponses et les champs sont correctement remplis (*)",
                "required" => true,
                "mapped" => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data-class" => User::class,
        ]);
    }
}
