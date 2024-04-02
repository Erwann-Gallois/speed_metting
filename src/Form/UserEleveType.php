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
                'label' => 'form.nom',
                'attr' => [
                    'placeholder' => 'form.nom'
                ]
            ])
        ->add("filiere", TextType::class, [
                'label' => "form.filiere",
                'attr' => [
                    'placeholder' => "form.filiere"
                ],
                "required" => false,
            ])
            ->add('prenom', TextType::class, [
                'label' => 'form.prenom',
                'attr' => [
                    'placeholder' => 'form.prenom'
                ],
                "required" => false,
            ])
            ->add('imageFile', VichFileType::class, [
                'label' => 'form.img',
                'attr' => [
                    'placeholder' => 'form.img'
                ],
                "allow_delete" => true,
                "download_label" => "label.download",
                "download_uri" => true,
                // "image_uri" => true,
                // "imagine_pattern" => "squared_thumbnail_small",
                "required" => false,
                "mapped" => false,
            ])
            ->add('question', TextareaType::class, [
                'label' => "form.quest_etud",
                'attr' => [
                    'placeholder' => "form.quest_etud",
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
