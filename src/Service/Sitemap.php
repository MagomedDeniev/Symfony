<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\Person;
use App\Entity\Song;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Sitemap
{
    public function __construct(
        private UrlGeneratorInterface $generator,
        private EntityManagerInterface $em,
        private Environment $twig
    ){}

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function urls($hostname): Response
    {
        $urls = [];

        // Static urls
        $urls[] = [
            'loc' => $this->generator->generate('app_home'),
            'changefreq' => 'hourly'
        ];
        $urls[] = [
            'loc' => $this->generator->generate('song_index'),
            'changefreq' => 'hourly'
        ];
        $urls[] = [
            'loc' => $this->generator->generate('song_chart', [
                'chart' => 'trends'
            ]),
            'changefreq' => 'daily'
        ];
        $urls[] = [
            'loc' => $this->generator->generate('song_chart', [
                'chart' => 'novelty'
            ]),
            'changefreq' => 'daily'
        ];

        $urls[] = ['loc' => $this->generator->generate('app_login')];
        $urls[] = ['loc' => $this->generator->generate('app_register')];
        $urls[] = ['loc' => $this->generator->generate('app_account_recovery')];

        // Song urls
        foreach ($this->em->getRepository(Song::class)->findBy(['status' => true]) as $song) {
            $urls[] = [
                'loc' => $this->generator->generate('song_show', [
                    'slug' => $song->getSlug()
                ]),
                'lastmod' => $song->getEditingDate()->format('Y-m-d')
            ];
        }

        // People urls
        foreach ($this->em->getRepository(Person::class)->findActiveSongsPeopleByActivity('vocalist') as $person) {
            $urls[] = [
                'loc' => $this->generator->generate('person_show', [
                    'slug' => $person->getSlug()
                ]),
                'lastmod' => $person->getUpdatedAt()->format('Y-m-d')
            ];
        }

        // Response creation
        $response = new Response(
            $this->twig->render('interface/layouts/service/sitemap.html.twig', [
                'urls' => $urls,
                'hostname' => $hostname
            ]),
            200
        );

        // HTTP headers
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function postsUrls($hostname): Response
    {
        $urls = [];

        // Posts urls
        foreach ($this->em->getRepository(Post::class)->findBy(['status' => true]) as $post) {
            $urls[] = [
                'loc' => $this->generator->generate('post_show', [
                    'id' => $post->getId()
                ]),
                'lastmod' => $post->getUpdatedAt()->format('Y-m-d')
            ];
        }

        // Response creation
        $response = new Response(
            $this->twig->render('interface/layouts/service/sitemap.html.twig', [
                'urls' => $urls,
                'hostname' => $hostname
            ]),
            200
        );

        // HTTP headers
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
