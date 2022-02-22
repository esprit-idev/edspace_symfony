<?php

namespace App\Form;

use App\Entity\CategorieClub;
use App\Entity\Club;
use App\Entity\User;
use App\Repository\ClubRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClubType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('clubNom', TextType::class, [
                'label' => 'Nom du club ',
                'attr' => [
                    'placeholder' => 'Choisissez un nom',
                    'class' => 'name'
                ]

            ])
            ->add('clubResponsable', EntityType::class, [
                'label' => 'Email du responsable ',
                'attr' => [
                    'placeholder' => "ex@ex.com",
                    'class' => 'name'
                ],
                'class' => User::class,

                'placeholder' => 'Choisissez unrespo',
                'query_builder' => function(UserRepository $repository) {

                    $qb = $repository->createQueryBuilder('u');

                    return $qb
                        // find all users where 'deleted' is NOT '1'
                        ->where('u.role != 1')
                        ;
                },
                'choice_label' => 'email',


            ])
            ->add('clubDescription', TextareaType::class, [
                'label' => 'Description ',
                'attr' => [
                    'placeholder' => 'Description du club',
                    'class' => 'name'
                ]
            ])
            ->add('clubCategorie', EntityType::class, [
                'label' => 'Catégorie ',
                'class' => CategorieClub::class,
                'choice_label' => 'categorieNom',
                'placeholder' => 'Choisissez une catégorie',

            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Club::class,
        ]);
    }
}
