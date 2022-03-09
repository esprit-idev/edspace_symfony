<?php

namespace App\Form;

use App\Entity\Niveau;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NiveauType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id',
                TextType::class, [
                'label' => "Niveau d'étude ",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Niveau::class,
            'constraints' => [
                new UniqueEntity(['fields' => ['id'], 'entityClass' => 'App\Entity\Niveau', 'message' => 'Ce niveau existe déjà'])
            ],
        ]);
    }
}
