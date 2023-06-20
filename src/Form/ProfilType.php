<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('fullName', TextType::class, [
            'label' => 'Nom et prénom',

        ])
        ->add('password', PasswordType::class, [
            'label' => 'Mot de passe actuel',
            'required' => false
        ])
        ->add('new_password', PasswordType::class, [
            'label' => 'Nouveau mot de passe',
            'mapped' => false,
            'required' => false,
            'constraints' => [
                new Length(['min' => 6, 'max' => 20, 'minMessage' => "Le mot de passe doit avoir au moins 6 caractères", 'maxMessage' => "Le mot de passe doit avoir moins de 20 caractères"])
            ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
