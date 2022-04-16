<?php

namespace App\Form;

use App\Entity\Notification;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', TextareaType::class,[
                'required' => false,
                'attr' => [
                    'class' => 'md-auto-sizer',
                    'placeholder' => 'Напишите причину отказа',
                    'rows' => 1
                ]
            ])
            ->add('approve', SubmitType::class, [
                'label' => 'approve',
                'attr' => [ 'class' => 'btn btn-modal-primary md-modal-item' ]
            ])
            ->add('reject', SubmitType::class, [
                'label' => 'reject',
                'attr' => [ 'class' => 'btn btn-modal-danger md-modal-item' ]
            ])
            ->add('gender', ChoiceType::class, [
                'mapped' => false,
                'choices' => [
                    'content.for.all' => null,
                    'content.for.male.audience' => false,
                    'content.for.female.audience' => true
                ],
                'data' => $options['gender'],
                'attr' => ['class' => 'chosen']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Notification::class,
            'gender' => null
        ]);
    }
}
