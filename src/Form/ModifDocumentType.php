<?php

namespace App\Form;

use App\Entity\Document;
use App\Entity\Matiere;
use App\Entity\Niveau;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ModifDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('niveau',
                EntityType::class,[
                    'class'=>Niveau::class,
                    'choice_label'=>'id',
                    'placeholder'=>'-- Sélectionnez un niveau --',
                    'label' => "Niveau d'étude ",
                ])
            ->add('matiere',
                EntityType::class,[
                    'class'=>Matiere::class,
                    'choice_label'=>'id',
                    'placeholder'=>'-- Sélectionnez une matière --',
                    'label' => 'Nom de la matière ',
                ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}
