<?php

namespace App\Twig;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ScriptsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('autoplay', [$this, 'autoplay'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('ckeditor', [$this, 'ckeditor'], ['is_safe' => ['html'], 'needs_environment' => true]),
            new TwigFunction('chosen', [$this, 'chosen'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function autoplay(Environment $twig): string
    {
        return $twig->render('interface/layouts/scripts/autoplay.html.twig');
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function ckeditor(Environment $twig, int $limit = null): string
    {
        return $twig->render('interface/layouts/scripts/ckeditor.html.twig', [
            'limit' => $limit
        ]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function chosen(Environment $twig, $limit = 3, $selector = '.chosen'): string
    {
        return $twig->render('interface/layouts/scripts/chosen.html.twig', [
            'limit' => $limit,
            'selector' => $selector
        ]);
    }
}
