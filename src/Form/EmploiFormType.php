<?php

namespace App\Form;

use App\Entity\CategorieEmploi;
use App\Entity\Emploi;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
class EmploiFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class,[
                'label' => 'Titre'
            ])
            ->add('content', TextareaType::class,[
                'label' => 'Description de proposition d\'emploi'
            ])
            ->add('categoryName', EntityType::class,[
                    'class'=>CategorieEmploi::class,
                    'choice_label'=>'categoryName',
                    'placeholder'=>'-- Sélectionnez une catégorie --',
                    'label' => "Catégorie ",
            ])

            ->add('date', DateType::class,[
                'label' => 'date',
                'data' => new \DateTime('now'),
            ])
            ->add('image', FileType::class,[
                'mapped'=> false,
                'required' => false,
                'label' => 'Image',
                'data_class' => null,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                            'image/svg',
                        ],
                        'mimeTypesMessage' => 'Ajoutez une image',
                    ])
                ],
            ])   
            ->add('ajouter', SubmitType::class,[
                'label' => 'Ajouter',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Emploi::class,
        ]);
    }
}
