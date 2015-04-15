<?php
namespace Hangman\Result;

use MiniGame\Result\GoodProposition;

class HangmanGoodProposition extends HangmanProposition implements GoodProposition {

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return sprintf('Well played! %s (letters played: %s) - Remaining chances: %d', $this->getFeedBack(), $this->getPlayedLettersAsString(), $this->remainingChances);
    }
}