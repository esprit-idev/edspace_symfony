<?php

namespace App\Form;

use App\Entity\ClubPub;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClubPubType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pubDescription',TextareaType::class,[
                'label'=>'Description ',
                'attr'=>[
                    'placeholder'=>'Ecrivez quelque chose ici',
                    'class'=>'name'
                ]
            ])
           /* ->add('pubFile',FileType::class,[
                'label'=>'Joindre un fichier ',
            ])*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ClubPub::class,
        ]);
    }
}
