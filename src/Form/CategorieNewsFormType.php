<?php

namespace App\Form;

use App\Entity\CategorieNews;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorieNewsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categoryName', TextType::class,[
                'label' => 'Nom de la catégorie',
                'required' => true,
            ])
            ->add('ajouter', SubmitType::class,[
                'label' => 'Ajouter',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CategorieNews::class,
            'constraints' => [
                new UniqueEntity(['fields'=>'categoryName','entityClass'=>'App\Entity\CategorieNews','message' => 'Cette categorie existe déjà' ])
            ],
        ]);
    }
}
