<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserChangeMPDType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ancien_mdp', PasswordType::class, [
                'label' => 'form.last_mdp',
                'attr' => [
                    'placeholder' => 'form.last_mdp'
                ],
            ])
            ->add('plainPassword1', PasswordType::class, [
                'label' => 'form.new_mdp', 
                'attr' => ['id' => 'new-password'],
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'form.constraint.new_mdp.notblank',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'form.constraint.new_mdp.limit',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('plainPassword2', PasswordType::class, [
                'label' => 'form.new_repeat_mdp',
                'attr' => ['id' => 'repeat-password'],
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'form.constraint.new_mdp.notblank',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'form.constraint.new_mdp.limit',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // 'data_class' => User::class,
        ]);
    }
}
