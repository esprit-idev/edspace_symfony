<?php

namespace App\Form;

use App\Entity\CategorieClub;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorieClubType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('categorieNom',TextType::class,[
                'label'=>'Categorie ',
                'attr'=>[
                    'placeholder'=>'Merci de définir une nouvelle catégorie',
                    'class'=>'name'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CategorieClub::class,
        ]);
    }
}
