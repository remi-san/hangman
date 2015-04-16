<?php
namespace Hangman\Move;

use MiniGame\Move;

class Proposition implements Move {

    /**
     * @var string
     */
    private $text;

    /**
     * @param string $text
     */
    public function __construct($text) {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
} 