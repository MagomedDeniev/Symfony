<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'username',
                'help' => 'username.help',
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'max' => 28,
                        'minMessage' => 'username.min.length.message',
                        'maxMessage' => 'username.max.length.message'
                    ])
                ],
                'attr' => [
                    'class' => 'username-input',
                    'maxlength' => 28,
                    'minlength' => 6
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'email'
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'password.not.same',
                'second_options' => ['label' => 'confirm.password'],
                'first_options' => [
                    'label' => 'password',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'password.is.empty',
                        ]),
                        new Length([
                            'min' => 6,
                            'max' => 80,
                            'minMessage' => 'password.min.length.message',
                            'maxMessage' => 'password.max.length.message'
                        ]),
                    ],
                    'attr' => [
                        'minlength' => 6,
                        'maxlength' => 80,
                    ]
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'gender',
                'mapped' => false,
                'expanded' => true,
                'label_attr' => ['class' => 'radio-custom'],
                'choices' => [
                    'gender.male' => 0,
                    'gender.female' => 1
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
                'mapped' => false,
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
            ->add('invitedBy', TextType::class, [
                'label' => 'invited.by',
                'help' => 'invited.by.help',
                'mapped' => false,
                'required' => false
            ])
//            ->add('agreeTerms', CheckboxType::class, [
//                'mapped' => false,
//                'label_attr' => ['class' => 'checkbox-custom'],
//                'constraints' => [
//                    new IsTrue([
//                        'message' => 'agree.terms.message',
//                    ])
//                ]
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
