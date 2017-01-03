<?php

namespace Hangman;

class Word
{
    private $word;

    /**
     * Word constructor.
     *
     * @param $word
     */
    public function __construct($word)
    {
        $this->word = strtoupper($word);
    }

    /**
     * Get the letters of the word
     *
     * @return string[]
     */
    public function getLetters()
    {
        $letters = array_unique(str_split(strtoupper($this->word)));
        sort($letters);
        return $letters;
    }

    /**
     * Build the word from played letters
     *
     * @param string[] $playedLetters
     *
     * @return string
     */
    public function buildWord($playedLetters)
    {
        $wordLetters = $this->getLetters();
        $playedLetters = array_map(function ($letter) {
            return strtoupper($letter);
        }, $playedLetters);
        $goodLetters = array_intersect($wordLetters, $playedLetters);
        $splitWord = str_split($this->word);

        $word = '';
        foreach ($splitWord as $letter) {
            $word .= (in_array($letter, $goodLetters) ? $letter : '_') . ' ';
        }

        return trim($word);
    }

    /**
     * Checks if the answer is valid
     *
     * @param string $answer
     *
     * @return bool
     */
    public function isValid($answer)
    {
        return strlen($answer) === strlen($this->word);
    }

    /**
     * Returns if the letter is contained in the word
     *
     * @param string $letter
     *
     * @return boolean
     */
    public function contains($letter)
    {
        return strpos($this->word, strtoupper($letter)) !== false;
    }

    /**
     * @param string $word
     *
     * @return bool
     */
    public function equals($word)
    {
        return ($this->word === strtoupper($word));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->word;
    }
}
