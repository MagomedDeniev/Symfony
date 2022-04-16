<?php

namespace App\Service;

use App\Entity\Tag;
use App\Repository\PostRepository;
use App\Repository\SongRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class Searcher
{
    public function __construct(
        private TagRepository $tagRepo,
        private UserRepository $userRepo,
        private SongRepository $songRepo,
        private PostRepository $postRepo,
        private RequestStack $requestStack,
        private FormFactoryInterface $formFactory,
        private TranslatorInterface $translator,
        private Compiler $compiler,
        private ?string $keyword = null
    ) {}

    public function searchForm($keyword): FormInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        $form = $this->formFactory->createBuilder(FormType::class)
            ->add('keyword', SearchType::class, [
                'label' => 'search',
                'attr' => ['value' => (!$this->isTagsSearch()) ? $keyword : null, 'placeholder' => 'search']
            ])
            ->getForm();

        $form->handleRequest($request);

        return $form;
    }

    public function tagsForm(): FormInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        $tagsForm = $this->formFactory->createBuilder(FormType::class)
            ->add('tags', EntityType::class, [
                'label' => 'tags',
                'class' => Tag::class,
                'required' => false,
                'multiple' => true,
                'choice_label' => 'title',
                'choices' => $this->tagRepo->findBy(['type' => 'song']),
                'label_attr' => ['class' => 'checkbox-custom'],
                'attr' => [
                    'data-placeholder' => $this->trans('select.tags') . '..',
                    'class' => 'chosen',
                    'value' => 'Рок'
                ],
                'data' => ($this->isTagsSearch()) ? $this->getSearchedTags() : null
            ])
            ->add('tagSearch', SubmitType::class, [
                'label' => 'search'
            ])
            ->getForm();

        $tagsForm->handleRequest($request);

        return $tagsForm;
    }

    public function getSearchedTags(): array
    {
        $searchedTags = [];
        foreach ($this->compiler->tagsSearchToArray($this->keyword) as $tagSlug) {
            $searchedTags[] = $this->tagRepo->findOneBy(['slug' => $tagSlug]);
        }
        return $searchedTags;
    }

    public function getSearchedTagsData(): ?array
    {
        if ($this->isTagsSearch()) {
            $tags = [
                'data' => $this->getSearchedTags(),
                'plural' => (count($this->getSearchedTags()) > 1) ? 'tags' : 'tag',
                'string' => $this->compiler->tagsString($this->getSearchedTags())
            ];
        } else {
            $tags = null;
        }

        return $tags;
    }

    public function getData($type, $data = null): array
    {
        if ($type === 'users') {
            return $this->userRepo->findByKeyword($this->keyword,['registeredAt' => 'DESC'],5);
        }

        if ($data === null) {
            if ($type === 'songs') {
                $repo = $this->songRepo->findByKeyword($this->keyword, ['releaseDate' => 'DESC'],5);
            } else {
                $repo = $this->postRepo->findByKeyword($this->keyword, [],5);
            }
        } else {
            $repo = $data;
        }

        $output = [];

        foreach ($repo as $entity) {
            ($type === 'songs') ? $text = $entity->getLyrics() : $text = $entity->getContent();
            $result = $this->compiler->matchInText($this->compiler->htmlToText($text),$this->keyword);

            $output[] = [
                'info' => $entity,
                'words' => str_replace($this->keyword,'<span class="found-keyword">' . $this->keyword . '</span>', $result)
            ];
        }

        return $output;
    }

    public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    public function isTagsSearch(): ?bool
    {
        return ($this->keyword) ? str_contains($this->keyword,'tags=') : null;
    }

    public function setKeyword($keyword): void
    {
        $this->keyword = mb_strtolower($keyword);
    }
}
