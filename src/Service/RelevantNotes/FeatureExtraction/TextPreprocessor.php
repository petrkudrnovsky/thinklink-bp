<?php

namespace App\Service\RelevantNotes\FeatureExtraction;

use x3wil\CzechStemmer;

class TextPreprocessor
{
    public function __construct(
        private array $stopWordsCz,
    )
    {
    }

    /**
     * @param string $text
     * @return string[]
     */
    public function preprocess(string $text): array
    {
        $text = $this->removeCzechDiacritics($text);
        $text = $this->removeSpecialCharacters($text);
        $text = $this->toLowerCase($text);
        $tokens = $this->tokenize($text);
        $tokens = $this->removeStopWords($tokens);
        $tokens = $this->removeEmptyTokens($tokens);
        return $this->stem($tokens);
    }

    /**
     * @param string $text
     * @return string
     */
    public function removeSpecialCharacters(string $text): string
    {
        // Regex: remove everything except letters and spaces (even newlines, tabs, etc.), tested with: https://regex101.com/
        return preg_replace('/[^a-zA-Z ]/', '', $text);
    }

    /**
     * @param string $text
     * @return string
     */
    function removeCzechDiacritics(string $text): string
    {
        # Source: https://cs.wikipedia.org/wiki/Abeceda - for checking the diacritics
        $from = ['á','č','ď','é','ě','í','ň','ó','ř','š','ť','ú','ů','ý','ž','Á','Č','Ď','É','Ě','Í','Ň','Ó','Ř','Š','Ť','Ú','Ů','Ý','Ž'];
        $to   = ['a','c','d','e','e','i','n','o','r','s','t','u','u','y','z','A','C','D','E','E','I','N','O','R','S','T','U','U','Y','Z'];

        return str_replace($from, $to, $text);
    }

    /**
     * @param string $text
     * @return string
     */
    public function toLowerCase(string $text): string
    {
        return strtolower($text);
    }

    /**
     * @param string $text
     * @return string[]
     */
    public function tokenize(string $text): array
    {
        return explode(' ', $text);
    }

    /**
     * @param array $tokens
     * @return array
     */
    public function removeStopWords(array $tokens): array
    {
        return array_diff($tokens, $this->stopWordsCz);
    }

    public function removeEmptyTokens(array $tokens): array
    {
        foreach ($tokens as $key => $token) {
            if (empty($token)) {
                unset($tokens[$key]);
            }
        }
        return $tokens;
    }

    /**
     * @param array $tokens
     * @return array
     */
    public function stem(array $tokens): array
    {
        $stemmedTokens = [];
        # Source: https://github.com/x3wil/czech-stemmer
        // It is possible to use light or aggressive stemming
        $stemmer = new CzechStemmer();
        foreach ($tokens as $token) {
            $stemmedTokens[] = $stemmer->stemmLight($token);
        }

        return $stemmedTokens;
    }
}