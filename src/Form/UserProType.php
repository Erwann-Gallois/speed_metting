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
                'label' => 'form.nom',
                'attr' => [
                    'placeholder' => 'form.nom'
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'form.prenom',
                'attr' => [
                    'placeholder' => 'form.prenom'
                ]
            ])
            ->add('question', TextareaType::class, [
                'label' => "form.quest_pro1",
                'attr' => [
                    'placeholder' => "form.quest_pro1",
                    'class' => "form-control",
                    'rows' => 5,
                ],
                // 'required' => false,
            ])
            ->add("imageFile", VichFileType::class, [
                "label" => "form.img",
                "attr" => [
                    "placeholder" => "form.img"
                ],
                "allow_delete" => true,
                "download_label" => "label.download",
                "download_uri" => true,
                // "image_uri" => true,
                // "imagine_pattern" => "squared_thumbnail_small",
                "required" => false,
                "mapped" => false,
            ])
            ->add('entreprise', TextType::class, [
                'label' => 'form.entreprise',
                'attr' => [
                    'placeholder' => 'form.entreprise'
                ]
            ])
            ->add('etude', TextareaType::class, [
                'label' => 'form.quest_pro2',
                'attr' => [
                    'placeholder' => 'form.quest_pro2',
                    'rows' => 5
                ]
            ])
            ->add('poste', TextType::class, [
                'label' => 'form.poste',
                'attr' => [
                    'placeholder' => 'form.poste'
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
