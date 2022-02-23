<?php

namespace App\Form;

use App\Entity\ThreadType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThreadTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content',TextType::class,['attr' =>[
                'style' => 'width : 30%',
                'aria-describedby' => 'search-addon',
                'aria-label' => 'Search',
                'class'=>'form-control rounded',
                'placeholder' => 'Topic']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ThreadType::class,
        ]);
    }
}
