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
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

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
            ->add('plainPassword1', PasswordType::class, [
                'label' => 'Mot de Passe (*)', 
                'attr' => ['id' => 'new-password'],
                'required' => true,
                'mapped' => false,
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
                ],
            ])
            ->add('plainPassword2', PasswordType::class, [
                'label' => 'Mot de Passe (*)', 
                'attr' => ['id' => 'repeat-password'],
                'required' => true,
                'mapped' => false,
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
                ],
            ])
            ->add("imageFile", VichImageType::class, 
            [
                'label' => 'Photo de profil',
                'help' => 'Taille maximum : 8Mo',	
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => false,
                'delete_label' => 'Supprimer la photo',
                "required" => false,
                "mapped" => false,
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
