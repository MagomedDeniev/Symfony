<?php

namespace App\Form;

use App\Entity\Activity;
use App\Entity\Person;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'first.name'
            ])
            ->add('lastName', TextType::class, [
                'label' => 'last.name',
                'required' => false
            ])
            ->add('birthDay', DateType::class, [
                'label' => 'birth.date',
                'required' => false,
                'widget' => 'single_text'
            ])
            ->add('biography', TextareaType::class, [
                'label' => 'biography',
                'required' => false,
                'attr' => [
                    'class' => 'ckeditor'
                ]
            ])
            ->add('pictureFile', VichImageType::class, [
                'label' => 'image',
                'required' => false,
                'download_uri' => false,
                'image_uri' => false,
                'allow_delete' => false
            ])
            ->add('activity', EntityType::class, [
                'label' => 'activity',
                'class' => Activity::class,
                'multiple' => true,
                'expanded' => true,
                'choice_label' => 'title',
                'label_attr' => ['class' => 'checkbox-custom']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
