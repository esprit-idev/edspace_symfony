<?php

namespace App\Form;

use App\Entity\ClubPub;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ClubPubType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pubDescription',CKEditorType::class,[
                'label'=>'Description ',
                'attr'=>[
                    'placeholder'=>'Ecrivez quelque chose ici',
                    'class'=>'name'
                ]
            ])
            ->add('ClubImg', FileType::class, [

                'mapped'=> false,
                'required' => false,
                'label' => 'Image/Video',
                'data_class' => null,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpeg',
                            'video/mp4',
                        ],
                        'mimeTypesMessage' => 'Ajoutez image/video valid svp!',
                    ])
                ],
            ])
            ->add('pubFile', FileType::class, [

        'mapped'=> false,
        'required' => false,
        'label' => 'Ajoutez un fichier',
        'data_class' => null,
        'constraints' => [
            new File([
                'mimeTypes' => [
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'application/vnd.openxmlformats-officedocument.presentationml.slide',
                    'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
                    'application/vnd.openxmlformats-officedocument.presentationml.template',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
                    'application/vnd.ms-word',
                    'application/pdf',
                    'application/x-pdf'
                ],
                'mimeTypesMessage' => 'Ajoutez un fichier valide svp!',
            ]),

        ],
    ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ClubPub::class,
        ]);
    }
}
