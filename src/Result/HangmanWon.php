<?php
namespace Hangman\Result;

use MiniGame\Result\GameWon;

class HangmanWon extends HangmanEndGame implements GameWon {

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return sprintf('Congratulations! The word was %s.', $this->getSolution());
    }
}