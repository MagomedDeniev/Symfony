<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'second_options' => ['label' => 'confirm.password', 'help' => 'confirm.password.help'],
                'invalid_message' => 'password.not.same',
                'first_options' => [
                    'label' => 'new.password',
                    'constraints' => [
                        new NotBlank([
                            'message' => 'password.is.empty',
                        ]),
                        new Length([
                            'min' => 6,
                            'max' => 80,
                            'minMessage' => 'password.min.length.message',
                            'maxMessage' => 'password.min.length.message',
                        ]),
                    ],
                    'attr' => [
                        'minlength' => 6,
                        'maxlength' => 80,
                    ]
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
