<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                'label' => "Qu'est qui est le plus passionnant dans votre métier ?",
                'attr' => [
                    'placeholder' => "Qu'est qui est le plus passionnant dans votre métier ?",
                    'class' => "form-control",
                    'rows' => 5,
                ],
                // 'required' => false,
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
