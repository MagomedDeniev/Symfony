<?php

namespace App\Controller;

use App\CustomAbstracts\CustomAbstractController;
use App\Entity\Post;
use App\Entity\Song;
use App\Entity\User;
use App\Service\Compiler;
use App\Service\Searcher;
use App\Service\Paginator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(name: 'search_',  methods: ['GET', 'POST'])]
class SearchController extends CustomAbstractController
{
    #[Route('/search/{keyword}', name: 'index')]
    public function index($keyword, Searcher $searcher): Response
    {
        $searcher->setKeyword($keyword);
        $form = $searcher->searchForm($keyword);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('search_index',[
                'keyword' => $form->get('keyword')->getData()
            ]);
        }

        return $this->render('interface/search/index.html.twig', [
            'form' => $form->createView(),
            'keyword' => mb_strtolower($keyword),
            'songs' => $searcher->getData('songs'),
            'posts' => $searcher->getData('posts'),
            'users' => $searcher->getData('users')
        ]);
    }

    #[Route('/search/songs/{keyword}/{page<\d+>?1}', name: 'songs')]
    public function songs($keyword, $page, Paginator $paginator, Searcher $searcher, Compiler $compiler): Response
    {
        $searcher->setKeyword($keyword);
        $form = $searcher->searchForm($keyword);
        $tagsForm = $searcher->tagsForm();

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('search_songs',[
                'keyword' => $form->get('keyword')->getData()
            ]);
        } elseif ($tagsForm->isSubmitted() && $tagsForm->isValid()) {
            return $this->redirectToRoute('search_songs',[
                'keyword' => $compiler->arrayToTagsSearch($tagsForm->get('tags')->getData()),
                'tagsForm' => $tagsForm
            ]);
        }

        $paginator->setClass(Song::class)
            ->setOrder(['releaseDate' => 'DESC'])
            ->setParameters(['keyword' => $keyword])
            ->setLimit(10)
            ->setPage($page);

        if ($searcher->isTagsSearch()) {
            $paginator  ->setMethod('findByTags')
                        ->setCriteria($compiler->tagsSearchToArray($keyword));
        } else {
            $paginator  ->setMethod('findByKeyword')
                        ->setCriteria($compiler->htmlToText($keyword));
        }

        return $this->render('interface/search/songs.html.twig', [
            'keyword' => $keyword,
            'page' => $page,
            'songs' => $searcher->getData('songs', $paginator->getData()),
            'paginator' => $paginator,
            'form' => $form->createView(),
            'tagsForm' => $tagsForm->createView(),
            'tags' => $searcher->getSearchedTagsData()
        ]);
    }

    #[Route('/search/posts/{keyword}/{page<\d+>?1}', name: 'posts')]
    public function posts($keyword, $page, Paginator $paginator, Searcher $searcher): Response
    {
        $searcher->setKeyword($keyword);
        $form = $searcher->searchForm($keyword);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('search_posts',[
                'keyword' => $form->get('keyword')->getData()
            ]);
        }

        $paginator
            ->setClass(Post::class)
            ->setMethod('findByKeyword')
            ->setOrder([])
            ->setParameters(['keyword' => $keyword])
            ->setCriteria($keyword)
            ->setLimit(20)
            ->setPage($page)
        ;

        return $this->render('interface/search/posts.html.twig', [
            'keyword' => $keyword,
            'page' => $page,
            'posts' => $searcher->getData('posts', $paginator->getData()),
            'paginator' => $paginator,
            'form' => $form->createView()
        ]);
    }

    #[Route('/search/users/{keyword}/{page<\d+>?1}', name: 'users')]
    public function users($keyword, $page, Paginator $paginator, Searcher $searcher): Response
    {
        $form = $searcher->searchForm($keyword);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('search_users',[
                'keyword' => $form->get('keyword')->getData()
            ]);
        }

        $paginator
            ->setClass(User::class)
            ->setMethod('findByKeyword')
            ->setOrder(['registeredAt' => 'DESC'])
            ->setParameters(['keyword' => $keyword])
            ->setCriteria($keyword)
            ->setLimit(20)
            ->setPage($page)
        ;

        return $this->render('interface/search/users.html.twig', [
            'keyword' => $keyword,
            'page' => $page,
            'paginator' => $paginator,
            'users' => $paginator->getData(),
            'form' => $form->createView()
        ]);
    }
}
