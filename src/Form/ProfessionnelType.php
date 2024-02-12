<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfessionnelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => "Nom du professionnel",
                "required" => true,
            ])
            ->add('prenom', TextType::class, [
                'label' => "Prénom du professionnel",
                "required" => true,
            ])
            ->add("email", EmailType::class, [
                "label" => "Email du professionnel",
                "required" => true
            ])
            ->add('poste', TextType::class, [
                "label" => "Intitulé du poste",
                "required"=> false
            ])
            ->add('entreprise', TextType::class, [
                "label" => "Nom de l'entreprise",
                "required"=> false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
