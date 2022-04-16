<?php

namespace App\Form;

use App\Entity\Song;
use App\Entity\Post;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\FollowRepository;
use App\Repository\PlaylistSongRepository;
use App\Repository\SongRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;

class PostType extends AbstractType
{
    private $translator;
    private $songs;
    private $user;
    private $role;
    private $tags;
    private $playlistSongs;
    private $follows;
    private $users;

    public function __construct(Security $security, AuthorizationCheckerInterface $authorizationChecker, TranslatorInterface $translator, SongRepository $songs, TagRepository $tags, PlaylistSongRepository $playlistSongs, FollowRepository $follows, UserRepository $users)
    {
        $this->user = $security->getUser();
        $this->role = $authorizationChecker;
        $this->translator = $translator;
        $this->songs = $songs;
        $this->tags = $tags;
        $this->playlistSongs = $playlistSongs;
        $this->follows = $follows;
        $this->users = $users;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $builder
//            ->add('tags', EntityType::class, [
//                'label' => 'categories',
//                'help' => 'post.tags.help',
//                'class' => Tag::class,
//                'multiple' => true,
//                'required' => false,
//                'choice_label' => 'title',
//                'choices' => $this->tags->findBy(['type' => 'post']),
//                'label_attr' => ['class' => 'checkbox-custom'],
//                'attr' => [
//                    'data-placeholder' => $this->translator->trans('select.categories'),
//                    'class' => 'chosen'
//                ]
//            ])
//        ;

        $post = $builder->getData();
        $userPlaylist = $this->playlistSongs->findOneBy(['user' => $this->user]);
        $userFollowing = $this->follows->findOneBy(['follower' => $this->user]);

        if (!$post->getId() || $post->getAuthor() == $this->user) {
            $builder
//                ->add('title', TextType::class, [
//                    'label' => 'heading',
//                    'help' => 'post.title.help',
//                    'required' => false,
//                    'constraints' => [
//                        new Length([
//                            'max' => 80,
//                            'maxMessage' => 'max.message'
//                        ])
//                    ]
//                ])
                ->add('content', TextareaType::class, [
                    'label' => 'description',
                    'required' => false,
                    'attr' => [
                        'style' => 'opacity:0;margin-bottom:20px',
                        'class' => 'ckeditor',
                        'rows' => 10
                    ],
                    'constraints' => [
                        new Length([
                            'max' => 5000,
                            'maxMessage' => 'max.message'
                        ])
                    ]
                ])
            ;
        }

        if (!$post->getId() || $post->getId() && $post->getAuthor() == $this->user && $post->getPublishedAt()->getTimestamp() === $post->getUpdatedAt()->getTimestamp()) {
            $builder
                ->add('imageFile', VichImageType::class, [
                    'label' => 'image',
                    'required' => false,
                    'download_uri' => false,
                    'image_uri' => false,
                    'allow_delete' => false
                ])
            ;
        }

        if (!$post->getId() && $userPlaylist !== null || $post->getAuthor() == $this->user && $userPlaylist !== null ) {
            $builder
                ->add('songs', EntityType::class, [
                    'label' => 'music',
                    'help' => 'song.help',
                    'class' => Song::class,
                    'multiple' => true,
                    'required' => false,
                    'choice_label' => 'FullTitle',
                    'label_attr' => ['class' => 'checkbox-custom'],
                    'choices' => $this->songs->findUserPlaylist(['user'=>$this->user]),
                    'attr' => [
                        'data-placeholder' => $this->translator->trans('select.song'),
                        'class' => 'chosen-songs'
                    ]
                ])
            ;
        }

        if (!$post->getId() && $userFollowing !== null || $post->getAuthor() == $this->user && $userFollowing !== null ) {
            $builder
                ->add('taggedUsers', EntityType::class, [
                    'label' => 'users',
                    'help' => 'tagged.users.help',
                    'class' => User::class,
                    'multiple' => true,
                    'required' => false,
                    'choice_label' => 'username',
                    'label_attr' => ['class' => 'checkbox-custom'],
                    'choices' => $this->users->findFollows(['user' => $this->user, 'type' => 'following']),
                    'attr' => [
                        'data-placeholder' => $this->translator->trans('select.users'),
                        'class' => 'chosen-users'
                    ]
                ])
            ;
        }

        if ($post->getId() && $post->getStatus() !== null && $this->role->isGranted('ROLE_POST_MODERATOR') && $post->getAuthor() !== $this->user) {
            $builder
                ->add('moderation', CheckboxType::class, [
                    'label' => 'to.moderation',
                    'required' => false,
                    'mapped' => false,
                    'label_attr' => ['class' => 'switch-custom']
                ])
                ->add('gender', ChoiceType::class, [
                    'choices' => [
                        'content.for.all' => null,
                        'content.for.male.audience' => false,
                        'content.for.female.audience' => true
                    ],
                    'attr' => ['class' => 'chosen']
                ])
                ->add('featured', CheckboxType::class, [
                    'label' => 'featured',
                    'required' => false,
                    'label_attr' => ['class' => 'switch-custom']
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class
        ]);
    }
}
