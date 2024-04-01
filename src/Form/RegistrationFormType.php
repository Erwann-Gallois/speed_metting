<?php

namespace App\Form;

use App\Entity\User;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Text;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'form.mail',
                'attr' => [
                    "placeholder" => "form.mail"
                ],
                'required' => true,
            ])
            ->add('nom', TextType::class, [
                'label' => 'form.nom',
                'attr' => [
                    "placeholder" => "form.nom"
                ],
                'required' => true,
            ])
            ->add('prenom', TextType::class, [
                'attr' => ['placeholder' => 'form.prenom'],
                'label' => 'form.prenom',
                'required' => true,
            ])
            ->add('plainPassword1', PasswordType::class, [
                'label' => 'form.mdp1', 
                'attr' => ['placeholder' => "form.mdp1"],
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
                'label' => 'form.mdp2', 
                'attr' => ['placeholder' => 'form.mdp2'],
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
            ->add('numetud', IntegerType::class, [
                'label' => "form.numetud",
                'attr' => [
                    'placeholder' => "form.numetud",
                    'class' => "form-control",
                ],
                'required' => true,
            ])
            ->add("imageFile", VichImageType::class, 
            [
                'label' => 'form.img',
                'help' => 'Taille maximum : 8Mo',	
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => false,
                'delete_label' => 'Supprimer la photo',
                "required" => false,
                "mapped" => false,
            ])
            ->add('filiere', TextType::class, [
                'label' => "form.filiere",
                'attr' => [
                    'placeholder' => "form.filiere",
                    'class' => "form-control",
                ],
                'required' => true,
            ])
            ->add('question', TextareaType::class, [
                'label' => "form.quest_etud",
                'attr' => [
                    'placeholder' => "form.quest_etud",
                    'class' => "form-control",
                    'rows' => 5,
                ],
                'required' => true,
            ])
            ->add('session', ChoiceType::class, [
                'label' => "form.session",
                'required' => true,
                "choices" => [
                    "Session 1" => 1,
                    "Session 2" => 2
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
