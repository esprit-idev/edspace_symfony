<?php

namespace App\Form;

use App\Entity\Matiere;
use App\Entity\Niveau;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MatiereType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id',TextType::class, [
                'label' => 'Nom de la matière ',
            ])
            ->add('niveau',EntityType::class,[
                'class'=>Niveau::class,
                'choice_label'=>'id',
                'label' => "Niveau d'étude ",
                'placeholder'=>'-- Sélectionnez un niveau --'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Matiere::class,

    'constraints' => [
        new UniqueEntity(['fields' => ['id'], 'entityClass' => 'App\Entity\Matiere', 'message' => 'Cette matière existe déjà'])
    ],
        ]);
    }
}
