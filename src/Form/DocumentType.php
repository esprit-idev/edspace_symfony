<?php

namespace App\Form;

use App\Entity\Document;
use App\Entity\Matiere;
use App\Entity\Niveau;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom',
                TextType::class, [
                    'label' => "Nom du document ",
                ])
            ->add('fichier',FileType::class,[
                'label'=> 'Choisissez votre document '
            ])
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
                ])
            ->add('upload',
                SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}
