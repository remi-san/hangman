<?php
namespace Hangman\Event\Util;

use MiniGame\Result\Error;

abstract class HangmanErrorEvent extends HangmanBasicResultEvent implements Error
{
}
