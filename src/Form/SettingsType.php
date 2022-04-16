<?php

namespace App\Form;

use App\Entity\User;
use App\Service\Defender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class SettingsType extends AbstractType
{
    private $user;
    private $defender;

    public function __construct(Security $security, Defender $defender)
    {
        $this->user = $security->getUser();
        $this->defender = $defender;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'username',
                'mapped' => false,
                'attr' => [
                    'value' => $this->user->getUsername(),
                    'class' => 'username-input',
                    'maxlength' => 28,
                    'minlength' => 8
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'email',
                'help' => 'email.help',
                'attr' => [
                    'class' => ($options['user']->getEmail() != $options['user']->getConfirmedEmail()) ? 'is-invalid' : null,
                ]
            ])
            ->add('hideOnline', CheckboxType::class, [
                'label' => 'hide.online',
                'required' => false,
                'label_attr' => ['class' => 'switch-custom']
            ])
            ->add('closedAccount', CheckboxType::class, [
                'label' => 'closed.account',
                'required' => false,
                'label_attr' => ['class' => 'switch-custom']
            ])
        ;
        if ($this->defender->isGranted($this->user,'ROLE_OWNER') && $options['user'] !== $this->user) {
            $builder->add('password', PasswordType::class, [
                'label' => 'password',
                'mapped' => false,
                'required' => false
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'user' => null
        ]);
    }
}
