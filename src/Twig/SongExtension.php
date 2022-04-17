<?php

namespace App\Twig;

use App\Repository\SongRepository;
use Cocur\Slugify\Slugify;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SongExtension extends AbstractExtension
{
    public function __construct(
        private SongRepository $songs,
        private $letters = [
            'a' => 'а',
            'b' => 'б',
            'v' => 'в',
            'g' => 'г',
            'd' => 'д',
            'z' => 'з',
            'i' => 'и',
            'k' => 'к',
            'l' => 'л',
            'm' => 'м',
            'p' => 'п',
            'r' => 'р',
            's' => 'с',
            't' => 'т',
            'u' => 'у',
            'h' => 'х',
            'sh' => 'ш',
            'e' => 'э',
            'yu' => 'ю'
        ]
    ){}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('player', [$this, 'player'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('chartBox', [$this, 'chartBox'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('lettersMenu', [$this, 'lettersMenu'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('slugify', [$this, 'slugify'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    public function letters(): array
    {
        return $this->letters;
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function player(Environment $twig, $song, $download = null, $add = null, $type = null, $image = null): string
    {
        return $twig->render('interface/layouts/service/player.html.twig', [
            'song' => $song,
            'download' => $download,
            'add' => $add,
            'type' => $type,
            'image' => $image
        ]);
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function chartBox(Environment $twig, $chart): string
    {
        if ($chart == 'trends') {
            $songs = $this->songs->findByViews(['status' => true], [],5);
        } elseif ($chart == 'novelty') {
            $songs = $this->songs->findBy(['status' => true], ['releaseDate' => 'DESC'],5);
        } elseif ($chart == 'discussed') {
            $songs = $this->songs->findByDiscussed(['status' => true],[],5);
        } elseif ($chart == 'lasts') {
            $songs = $this->songs->findBy(['status' => true], ['publicationDate' => 'DESC'],5);
        } else {
            $songs = null;
        }

        return $twig->render('interface/layouts/service/chart_box.html.twig', [
            'songs' => $songs,
            'chart' => $chart
        ]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function lettersMenu(Environment $twig): string
    {
        return $twig->render('interface/layouts/service/letters.html.twig', [
            'letters' => $this->letters
        ]);
    }

    public function slugify(Environment $twig, $data): string
    {
        $slugify = new Slugify();
        return $slugify->slugify($data);
    }
}
