<?php

namespace Hangman\Result;

use MiniGame\GameResult;

interface HangmanGameResult extends GameResult
{
    /**
     * @return array
     */
    public function getPlayedLetters();

    /**
     * @return int
     */
    public function getRemainingLives();
}
