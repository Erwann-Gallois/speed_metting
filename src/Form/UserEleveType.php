<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichFileType;

class UserEleveType extends AbstractType
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
        ->add("filiere", TextType::class, [
                'label' => "Filière",
                'attr' => [
                    'placeholder' => "Filière"
                ],
                "required" => false,
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'attr' => [
                    'placeholder' => 'Prénom'
                ],
                "required" => false,
            ])
            ->add('imageFile', VichFileType::class, [
                'label' => 'Image de profil',
                'attr' => [
                    'placeholder' => 'Image de profil'
                ],
                "allow_delete" => true,
                "download_label" => "Télécharger",
                "download_uri" => true,
                // "image_uri" => true,
                // "imagine_pattern" => "squared_thumbnail_small",
                "required" => false,
                "mapped" => false,
            ])
            ->add('question', TextareaType::class, [
                'label' => "Decrivez votre projet d'avenir",
                'attr' => [
                    'placeholder' => "Decrivez votre projet d'avenir",
                    'class' => "form-control",
                    'rows' => 5,
                ],
                'required' => false,
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
