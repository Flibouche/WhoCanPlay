<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class RomanNumeralTitleExtension extends AbstractExtension
{
    // Je crée une nouvelle fonctionnalité pour Twig
    public function getFilters()
    {
        // Je crée un nouveau filtre Twig
        return [
            new TwigFilter('title_with_roman', [$this, 'titleWithRoman']),
        ];
    }

    // Je crée une fonction qui sera appelée par le filtre Twig
    public function titleWithRoman($string)
    {
        // Je découpe la chaîne de caractères en mots
        $words = explode(' ', $string);
        // Je transforme chaque mot en majuscule si c'est un nombre romain
        $result = array_map(function($word) {
            if (preg_match('/^[IVXLCDM]+$/i', $word)) {
                return strtoupper($word);
            }
            return ucfirst(strtolower($word));
        }, $words);
        return implode(' ', $result);
    }
}