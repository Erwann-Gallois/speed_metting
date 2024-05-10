<?php

namespace App\Form;

use App\Entity\Variable;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VariableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_fin_inscription', DateTimeType::class, [
                "label" => false,
                "placeholder" => "Date de fin pour l'inscription",
                'required' => false,
                // "view_timezone" => "Europe/Paris"
            ])
            ->add('date_ouver_resa', DateTimeType::class, [
                "label" => false,
                "placeholder" => "Date d'ouverture des RDVs",
                'required' => false,
                // "view_timezone" => "Europe/Paris"
            ])
            ->add('date_fin_resa', DateTimeType::class, [
                "label" => false,
                "placeholder" => "Date de fin pour réservation des RDVs",
                'required' => false,
                // "view_timezone" => "Europe/Paris"
            ])
            ->add('place_session', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nouvelle limite de places par session',
                    'class' => 'form-control',
                ],
                'required' => false,
            ])
            ->add('place_rdv', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nouvelle limite de places par RDVs - Session 1',
                    'class' => 'form-control',
                ],
                'required' => false,
            ])
            ->add('place_rdv2', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nouvelle limite de places par RDVs - Session 2',
                    'class' => 'form-control',
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // 'data_class' => Variable::class,
        ]);
    }
}
