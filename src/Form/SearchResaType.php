<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchResaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('eleve', EntityType::class, [
                'class' => User::class,
                'choice_label'=> function (User $user) {
                    return $user->getNom() . ' ' . $user->getPrenom();
                },
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                    ->where('u.type = :type')
                    ->setParameter('type', 2)
                    ->orderBy('u.nom', "ASC");
                },
                'label' => 'Elève',
                'required' => false,
            ])
            ->add('pro', EntityType::class, [
                'class' => User::class,
                'choice_label'=> function (User $user) {
                    return $user->getNom() . ' ' . $user->getPrenom();
                },
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                    ->where('u.type = :type')
                    ->setParameter('type', 1)
                    ->orderBy('u.nom', "ASC");
                },
                'label' => 'Professionnel',
                'required' => false,
            ])
            ->add('heure', TimeType::class, [
                'label' => 'Heure',
                'required' => false,

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
