<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FilterExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('tagger', [$this, 'taggerFilter'])
        ];
    }

    public function taggerFilter($content)
    {
        return $content;
    }
}
