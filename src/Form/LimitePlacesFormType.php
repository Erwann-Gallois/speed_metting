<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class LimitePlacesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('limite_places', IntegerType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Nouvelle limite de places par groupe',
                    'class' => 'form-control',
                ],
                'required' => true,
            ]);
            // ->add('save', SubmitType::class, ['label' => 'Modifier']);
    }
}

