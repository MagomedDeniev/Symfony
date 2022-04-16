<?php

namespace App\Form;

use App\Entity\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'title'
            ])
            ->add('slug', TextType::class, [
                'label' => 'slug'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'description',
                'required' => false,
                'attr' => [
                    'style' => 'opacity:0;margin-bottom:20px',
                    'class' => 'ckeditor',
                    'rows' => 10
                ],
                'constraints' => [
                    new Length([
                        'max' => 800,
                        'maxMessage' => 'max.message'
                    ])
                ]
            ])
            ->add('type',ChoiceType::class, [
                'label' => 'type',
                'choices' => [
                    'song' => 'song',
                    'post' => 'post',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Tag::class,
        ]);
    }
}
