<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class UserProType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom' , TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Nom'
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Prénom'
                ]
            ])
            ->add('question', TextareaType::class, [
                'label' => "Qu'est-ce qui vous passionne dans votre métier ?",
                'attr' => [
                    'placeholder' => "Qu'est-ce qui vous passionne dans votre métier ?",
                    'class' => "form-control",
                    'rows' => 5,
                ],
                // 'required' => false,
            ])
            ->add("imageFile", VichFileType::class, [
                "label" => "Image de profil",
                "attr" => [
                    "placeholder" => "Image de profil"
                ],
                "allow_delete" => true,
                "download_label" => "Télécharger",
                "download_uri" => true,
                "image_uri" => true,
                "imagine_pattern" => "squared_thumbnail_small",
                "required" => false,
            ])
            ->add('entreprise', TextType::class, [
                'label' => 'Entreprise',
                'attr' => [
                    'placeholder' => 'Entreprise'
                ]
            ])
            ->add('etude', TextareaType::class, [
                'label' => 'Etude',
                'attr' => [
                    'placeholder' => 'Etude',
                    'rows' => 5
                ]
            ])
            ->add('poste', TextType::class, [
                'label' => 'Poste',
                'attr' => [
                    'placeholder' => 'Poste'
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
