<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username')
            ->add('prenom')
            ->add('email', EmailType::class , [
                'constraints'=>[
                    new NotBlank([
                        'message'=>'Merci de saisir une adresse email'
                    ])
                ],
                'required'=>true,
                'attr'=>[
                    'class'=>'form-control'
                ]
            ])
            ->add('password', PasswordType::class ,  [
                'empty_data' => '',
                /*'constraints'=>[
                    new NotBlank([
                        'message'=>'Merci de saisir un mot de passe'
                    ])
                ],*/
                'required'=>true,
                'attr'=>[
                    'class'=>'form-control'
                ]
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
