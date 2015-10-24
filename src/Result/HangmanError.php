<?php
namespace Hangman\Result;

use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\Result\Error;

class HangmanError implements HangmanGameResult, Error
{
    /**
     * @var MiniGameId
     */
    private $gameId;

    /**
     * @var PlayerId
     */
    private $playerId;

    /**
     * @var string
     */
    private $message;

    /**
     * @var array
     */
    private $lettersPlayed;

    /**
     * @var int
     */
    private $remainingChances;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param string     $message
     * @param array      $lettersPlayed
     * @param int        $remainingChances
     */
    public function __construct(
        MiniGameId $gameId,
        PlayerId $playerId,
        $message,
        array $lettersPlayed = array(),
        $remainingChances = null
    ) {
        $this->gameId = $gameId;
        $this->playerId = $playerId;
        $this->message = $message;
        $this->lettersPlayed = $lettersPlayed;
        $this->getRemainingLives();
    }

    /**
     * @return MiniGameId
     */
    public function getGameId()
    {
        return $this->gameId;
    }

    /**
     * @return PlayerId
     */
    public function getPlayerId()
    {
        return $this->playerId;
    }

    /**
     * @return array
     */
    public function getPlayedLetters()
    {
        return $this->lettersPlayed;
    }

    /**
     * @return int
     */
    public function getRemainingLives()
    {
        return $this->remainingChances;
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return $this->message;
    }
}
