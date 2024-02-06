<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmailSelectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Supposons que $options['emails'] contienne les e-mails à afficher
        $emails = $options['emails'];
        foreach ($emails as $index => $email) {
            $builder->add('email_' . $index, CheckboxType::class, [
                // 'label' => $email,
                'label' => false,
                'required' => false,
                'mapped' => false,
                'attr' => ['data-email' => $email]
            ]);
        }
        
        $builder->add('send', SubmitType::class, ['label' => 'Envoyer les e-mails']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'emails' => [],
        ]);
    }
}
