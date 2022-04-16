<?php

namespace App\Form;

use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('avatarFile', VichImageType::class, [
                'label' => 'avatar',
                'required' => false,
                'download_uri' => false,
                'image_uri' => false,
                'allow_delete' => false
            ])
            ->add('avatarDelete', CheckboxType::class, [
                'label' => 'delete.avatar',
                'required' => false,
                'mapped' => false,
                'label_attr' => ['class' => 'switch-custom']
            ])
            ->add('fullname', TextType::class, [
                'label' => 'full.name',
                'required' => false
            ])
            ->add('about', TextareaType::class, [
                'label' => 'about.me',
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
            ->add('url', UrlType::class, [
                'label' => 'website',
                'required' => false
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'gender',
                'expanded' => true,
                'label_attr' => ['class' => 'radio-custom'],
                'choices' => [
                    'gender.male' => '0',
                    'gender.female' => '1'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'gender.required.message'
                    ])
                ]
            ])
            ->add('birthday', DateType::class, [
                'label' => 'birth.date',
                'years' => range(date('Y')-10, date('Y')-100),
                'widget' => 'choice',
                'format' => 'ddMMMMyyyy',
                'constraints' => [
                    new NotBlank([
                        'message' => 'birthday.required'
                    ])
                ],
                'placeholder' => [
                    'year' => 'Год',
                    'month' => 'Месяц',
                    'day' => 'День',
                ],
                'attr' => [
                    'class' => 'user-birthday'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
