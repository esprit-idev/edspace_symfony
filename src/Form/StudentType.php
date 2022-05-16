<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Classe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class StudentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class ,
                ['label' => 'nom',
                    'empty_data' => ''])
            ->add('prenom', TextType::class ,
                ['label' => 'prenom',
                    'empty_data' => ''])
            ->add('email', EmailType::class , [
                'label' => 'email',
                'empty_data' => '',
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
            ->add('password', PasswordType::class,[
                'label' => 'Mot de passe',
                'empty_data' => '',
                /*'constraints'=>[
                    new NotBlank([
                        'message'=>'merci de saisir un mot de passe'
                    ])
                ],*/
                'required'=>true,
                'attr'=>[
                    'class'=>'form-control'
                ]
            ])
            ->add('isBanned')
            /*->add('roles', ChoiceType::class , [
                'choices'=>[
                    'Etudiant'=>'ROLE_STUDENT',
                    'Administrateur'=>'ROLE_ADMIN',
                    'Responsable du club'=>'ROLE_RESPONSABLEC'
                ],
                'expanded'=>true,
                'multiple'=>true,
                'label'=>'Roles'
            ])*/

            ->add('classe',EntityType::class,[
                'label' => 'Niveau',
                'class'=>Classe::class,
                'choice_label'=>'niveau'
            ])
            ->add('classe',EntityType::class,[
                'class'=>Classe::class,
                'choice_label'=>'classe'
            ])
            ->add('Ajouter',SubmitType::class)


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
