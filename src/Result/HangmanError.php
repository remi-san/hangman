<?php
namespace Hangman\Result;

use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\Result\Error;

class HangmanError extends HangmanGameResult implements Error
{
    /**
     * @var string
     */
    private $message;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $player
     * @param string     $message
     * @param array      $lettersPlayed
     * @param int        $remainingChances
     */
    public function __construct(
        MiniGameId $gameId,
        PlayerId $player,
        $message,
        array $lettersPlayed = array(),
        $remainingChances = null
    ) {
        $this->message = $message;
        parent::__construct($gameId, $player, $lettersPlayed, $remainingChances);
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return $this->message;
    }
}
