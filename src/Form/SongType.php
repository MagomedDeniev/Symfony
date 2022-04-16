<?php

namespace App\Form;

use App\Entity\Song;
use App\Entity\Person;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\PeopleRepository;
use App\Repository\SongRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

class SongType extends AbstractType
{
    private $translator;
    private $people;
    private $users;
    private $tags;
    private $role;

    public function __construct(TranslatorInterface $translator, PeopleRepository $peopleRepository, TagRepository $tags, UserRepository $users, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->translator = $translator;
        $this->people = $peopleRepository;
        $this->tags = $tags;
        $this->users = $users;
        $this->role = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lyrics', TextareaType::class, [
                'label' => 'lyrics',
                'required' => false,
                'attr' => [
                    'class' => 'ckeditor',
                    'rows' => 10
                ]
            ])
        ;

        if ($this->role->isGranted('ROLE_SONG_MODERATOR') or $this->role->isGranted('ROLE_SONG_EDITOR')) {
            $builder
                ->add('translation', TextareaType::class, [
                    'label' => 'translation',
                    'required' => false,
                    'attr' => [
                        'class' => 'ckeditor',
                        'rows' => 10
                    ]
                ])
                ->add('releaseDate', DateType::class, [
                    'label' => 'premiere.date',
                    'help' => 'premiere.date.help',
                    'required' => false,
                    'widget' => 'single_text'
                ])
                ->add('featuring', EntityType::class, [
                    'label' => 'featuring',
                    'class' => Person::class,
                    'multiple' => true,
                    'required' => false,
                    'choice_label' => 'fullName',
                    'label_attr' => ['class' => 'checkbox-custom'],
                    'choices' => $this->people->findPeopleByActivity('vocalist'),
                    'attr' => [
                        'data-placeholder' => $this->translator->trans('select.featuring'),
                        'class' => 'chosen'
                    ]
                ])
                ->add('tags', EntityType::class, [
                    'label' => 'tags',
                    'class' => Tag::class,
                    'required' => false,
                    'multiple' => true,
                    'choice_label' => 'title',
                    'choices' => $this->tags->findBy(['type' => 'song']),
                    'label_attr' => ['class' => 'checkbox-custom'],
                    'attr' => [
                        'data-placeholder' => $this->translator->trans('select.tags'),
                        'class' => 'chosen'
                    ]
                ])
            ;
        }

        if ($this->role->isGranted('ROLE_SONG_MODERATOR')) {
            $builder
                ->add('audioFile', VichFileType::class, [
                    'label' => 'song',
                    'download_uri' => false,
                    'allow_delete' => false,
                    'required' => false
                ])
                ->add('vocalist', EntityType::class, [
                    'label' => 'vocalist',
                    'class' => Person::class,
                    'choice_label' => 'fullName',
                    'label_attr' => ['class' => 'checkbox-custom'],
                    'choices' => $this->people->findPeopleByActivity('vocalist'),
                    'attr' => [
                        'class' => 'chosen'
                    ]
                ])
                ->add('title', TextType::class, [
                    'label' => 'title'
                ])
                ->add('status', ChoiceType::class, [
                    'label' => 'status',
                    'choices' => [
                        'Не опубликована' => null,
                        'На модерации' => false,
                        'Опубликована' => true
                    ],
                    'attr' => ['class' => 'chosen']
                ])
                ->add('updateTags', CheckboxType::class, [
                    'label' => 'update.tags',
                    'mapped' => false,
                    'required' => false,
                    'label_attr' => ['class' => 'switch-custom']
                ])
                ->add('author', EntityType::class, [
                    'label' => 'author',
                    'class' => User::class,
                    'required' => false,
                    'choice_label' => 'username',
                    'choices' => $this->users->findByRole('ROLE_SONG_AUTHOR'),
                    'label_attr' => ['class' => 'checkbox-custom'],
                    'attr' => [
                        'data-placeholder' => $this->translator->trans('select.author'),
                        'class' => 'chosen'
                    ]
                ])
                ->add('featured', CheckboxType::class, [
                    'label' => 'featured',
                    'required' => false,
                    'label_attr' => ['class' => 'switch-custom']
                ])
            ;
        }

        if ($this->role->isGranted('ROLE_SONG_AUTHOR') && !$this->role->isGranted('ROLE_SONG_EDITOR') && !$this->role->isGranted('ROLE_SONG_MODERATOR')) {
            $builder
                ->add('sendForModeration', CheckboxType::class, [
                    'label' => 'send.for.moderation',
                    'help' => 'send.song.for.moderation.help',
                    'mapped' => false,
                    'required' => false,
                    'label_attr' => ['class' => 'switch-custom']
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Song::class,
        ]);
    }
}
