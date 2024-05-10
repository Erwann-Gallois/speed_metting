<?php

namespace App\Form;

use App\Entity\Session;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModifResaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('heure', TimeType::class, [
                'label' => 'Heure',
                'required' => false,
            ])
            ->add('pro', EntityType::class, [
                'class' => User::class,
                'choice_label'=> function (User $user) {
                    return $user->getNom() . ' ' . $user->getPrenom();
                },
            ])
            ->add('eleve', EntityType::class, [
                'class' => User::class,
                'choice_label'=> function (User $user) {
                    return $user->getNom() . ' ' . $user->getPrenom();
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
        ]);
    }
}
