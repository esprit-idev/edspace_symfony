<?php

namespace App\Form;

use App\Entity\CategorieNews;
use App\Entity\PublicationNews;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PublicationNewsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',TextType::class, [
                'label' => 'Titre',
            ])
            ->add('content',TextareaType::class, [
                'label' => 'La Description ',
            ])
            ->add('owner',TextType::class, [
                'label' => 'L\'Auteur',
            ])
            ->add('date',DateType::class, [
                'label' => 'La date',
                'data' => new \DateTime('now'),
            ])
            ->add('categoryName',
                EntityType::class,[
                'class'=>CategorieNews::class,
                    'choice_label'=>'categoryName',
                    'placeholder'=>'-- Sélectionnez une catégorie --',
                    'label' => "Catégorie ",
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
                        ],
                        'mimeTypesMessage' => 'Ajoutez une image',
                    ])
                ],
            ])
            ->add('ajouter',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PublicationNews::class,
        ]);
    }
}
