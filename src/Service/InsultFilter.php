<?php

namespace App\Service;

class InsultFilter
{
    public function __construct(private array $insultList)
    {
        $this->insultList = ['stupid', 'idiot', 'dumb'];
    }

    public function filterInsults(string $text): string {
        foreach ($this->insultList as $insult) {
            // Je remplace chaque insulte par une série d'étoiles de la même longueur
            $replacement = str_repeat('*', strlen($insult));
            // J'utilise une expression régulière pour remplacer l'insulte en ignorant la casse
            $text = preg_replace('/\b' . preg_quote($insult, '/') . '\b/i', $replacement, $text);
        }

        return $text;
    }
}
