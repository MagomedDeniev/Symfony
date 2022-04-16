<?php

namespace App\Service;

class Compiler
{
    private array $symbols = [
        '<br>' => ' ',
        '<p>' => ' ',
        '</p>' => ' ',
        '<h5>' => ' ',
        '</h5>' => ' ',
        '<h6>' => ' ',
        '</h6>' => ' ',
        '<blockquote>' => '',
        '</blockquote>' => '',
        '<strong>' => '',
        '</strong>' => '',
        '<i>' => '',
        '</i>' => '',
        '<s>' => '',
        '</s>' => '',
        '<u>' => '',
        '</u>' => '',
        '&nbsp;' => '',
        '   ' => ' ',
        '  ' => ' '
    ];

    public function htmlToText($html, $lowercase = false)
    {
        if ($lowercase === false) {
            $this->symbols[','] = '';

            foreach ($this->symbols as $symbol => $replace) {
                $html = str_replace($symbol,$replace,$html);
            }

            return mb_strtolower($html);
        } else {
            foreach ($this->symbols as $symbol => $replace) {
                $html = str_replace($symbol,$replace,$html);
            }

            return $html;
        }
    }

    public function matchInText($text, $keyword): string
    {
        $keywordCount = str_word_count($keyword);
        $keywordPositionA = (int)(round(strpos($text,$keyword) / 2));
        $keywordPositionB = (int)((round(strpos($text,$keyword) / 2) + $keywordCount) / 2);

        if (($keywordPositionA - $keywordPositionB) < 30) {
            $keywordPositionA -= 30;
            $keywordPositionB += 30;
        }

        if (($keywordPositionA - $keywordPositionB) > 100) {
            $keywordPositionB /= 2.5;
        }

        if ($keywordPositionA < 20) {
            $keywordPositionB = $keywordPositionB + abs($keywordPositionA);
            $keywordPositionA = 0;
        }

        return mb_substr($text, $keywordPositionA, $keywordPositionB);
    }

    public function tagsSearchToArray($tags): array
    {
        return explode(',',str_replace('tags=','',$tags));
    }

    public function arrayToTagsSearch($array): string
    {
        $tags = [];

        foreach ($array as $tag) {
            $tags[] = $tag->getSlug();
        }

        return 'tags=' . implode(',',$tags);
    }

    public function tagsString($tags): string
    {
        $result = '';
        foreach ($tags as $key => $tag) {
            if ($key !== 0) {
                $result .= ', ';
            }
            $result .= $tag->getTitle();
        }
        return $result;
    }
}
