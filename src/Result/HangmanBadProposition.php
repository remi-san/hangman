<?php
namespace Hangman\Result;

use MiniGame\Result\MoveResult;

class HangmanBadProposition extends HangmanProposition implements MoveResult
{
    /**
     * @return string
     */
    public function getAsMessage()
    {
        return sprintf(
            'Too bad... %s (letters played: %s) - Remaining chances: %d',
            $this->getFeedBack(),
            $this->getPlayedLettersAsString(),
            $this->remainingChances
        );
    }
}
