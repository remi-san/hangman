<?php
namespace Hangman\Result;

use MiniGame\Result\GameLost;

class HangmanLost extends HangmanEndGame implements GameLost {

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return sprintf('You lose... The word was %s.', $this->getSolution());
    }
}