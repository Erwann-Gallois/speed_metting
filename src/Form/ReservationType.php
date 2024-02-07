<?php

namespace App\Form;

use App\Entity\Session;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('heure', ChoiceType::class, [
            //     'choices' => $this->slotRDV($options["session"]),
            //     'label' => 'Heure du rendez-vous',
            // ])
            ->add('pro', EntityType::class, [
                'class' => User::class,
                'choices' => $options['professionals'],
                'choice_label'=> function (User $user) {
                    return $user->getNom() . ' ' . $user->getPrenom(). ' - ' . $user->getPoste();
                },
                'query_builder' => function (\Doctrine\ORM\EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.type = 1');
                },
                'label' => false,
            ])
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
            'session' => null,
            'professionals' => null,
        ]);
    }
}
