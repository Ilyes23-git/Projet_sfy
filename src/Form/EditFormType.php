<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;

class EditFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Full Name',
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Full Name',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Telephone',
            ])

            ->add('adresse', TextType::class, [
                'label' => 'Address',
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
