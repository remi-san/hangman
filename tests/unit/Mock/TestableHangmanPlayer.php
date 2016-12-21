<?php

namespace Hangman\Test\Mock;

use Hangman\Entity\HangmanPlayer;

class TestableHangmanPlayer extends HangmanPlayer
{
    protected function apply($event)
    {
        $this->handleRecursively($event);
    }
}
