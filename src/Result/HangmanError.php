<?php
namespace Hangman\Result;

use MiniGame\Player;
use MiniGame\Result\Error;

class HangmanError extends HangmanGameResult implements Error {

    /**
     * @var string
     */
    private $message;

    /**
     * Constructor
     *
     * @param string $message
     * @param Player $player
     * @param array  $lettersPlayed
     * @param int    $remainingChances
     */
    public function __construct($message, Player $player, array $lettersPlayed = array(), $remainingChances = null) {
        $this->message = $message;
        parent::__construct($player, $lettersPlayed, $remainingChances);
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return $this->message;
    }
}