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
                'label' => "form.nom",
                "required" => true,
            ])
            ->add('prenom', TextType::class, [
                'label' => "form.prenom",
                "required" => true,
            ])
            ->add("email", EmailType::class, [
                "label" => "form.mail",
                "required" => true
            ])
            ->add('entreprise', TextType::class, [
                "label" => "form.entreprise",
                "required" => true,
            ])
            ->add('poste', TextType::class, [
                "label" => "form.poste",
                "required"=> false
            ])
            ->add('plainPassword1', PasswordType::class, [
                'label' => 'form.mdp1', 
                'attr' => ['id' => 'new-password'],
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'form.contraint.mdp1.not_blank',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'form.contraint.mdp1.limit',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('plainPassword2', PasswordType::class, [
                'label' => 'form.mdp2', 
                'attr' => ['id' => 'repeat-password'],
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'form.contraint.mdp1.not_blank',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'form.contraint.mdp1.limit',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add("imageFile", VichImageType::class, 
            [
                'label' => 'form.img',
                'help' => 'help.size_img',	
                'required' => false,
                'allow_delete' => true,
                'download_uri' => false,
                'image_uri' => false,
                'delete_label' => 'label.delete_img',
                "required" => false,
                "mapped" => false,
            ])
            ->add('question', TextareaType::class, [
                'label' => "form.quest_pro1",
                'attr' => [
                    'placeholder' => "form.quest_pro1",
                    'class' => "form-control",
                    'rows' => 5,
                ],
                'required' => true,
            ])
            ->add("etude", TextareaType::class, [
                "label" => "form.quest_pro2",
                "attr" => [
                    "placeholder" => "form.quest_pro2",
                    "class" => "form-control",
                    "rows" => 5,
                ],
                "required" => true,
            ])
            
            ->add("valid", CheckboxType::class, [
                "label" => "label.valid_info",
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
