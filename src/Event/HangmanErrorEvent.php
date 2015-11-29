<?php
namespace Hangman\Event;

use MiniGame\Result\Error;

interface HangmanErrorEvent extends Error
{
    /**
     * @return \Exception
     */
    public function getException();
}
