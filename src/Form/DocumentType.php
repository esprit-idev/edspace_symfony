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
        /*$formModifier = function (FormInterface $form, Niveau $niveau = null) {
            $matieres = null === $niveau ? [] : $niveau->getMatieres();

            $form->add('matiere', EntityType::class, [
                'class'=>Matiere::class,
                'choice_label'=>'id',
                'placeholder'=>'-- Sélectionnez une matière --',
                'label' => 'Nom de la matière ',
                'choices' => $matieres,
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                // this would be your entity, i.e. SportMeetup
                $data = $event->getData();

                $formModifier($event->getForm(), $data->getNiveau());
            }
        );

        $builder->get('niveau')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                // It's important here to fetch $event->getForm()->getData(), as
                // $event->getData() will get you the client data (that is, the ID)
                $niveau = $event->getForm()->getData();

                // since we've added the listener to the child, we'll have to pass on
                // the parent to the callback functions!
                $formModifier($event->getForm()->getParent(), $niveau);
            }
        );*/


}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}
