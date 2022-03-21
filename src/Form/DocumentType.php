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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

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
                'data_class' => null,
                'label'=> 'Choisissez votre document ',
                'constraints'=>[new NotBlank(['message'=>"L'attachement d'un fichier est requis"]),
                    new File([
                        'maxSize' => "327680k",
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/msword',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation','application/vnd.ms-powerpoint',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/vnd.ms-excel',
                            'application/pdf',
                            'application/zip',
                            'application/x-rar'
                        ],
                        'mimeTypesMessage'=>"Le fiichier importé est trop large"])
                ],
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
                ]);


}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}
