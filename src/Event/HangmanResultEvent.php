<?php
namespace Hangman\Event;

use Hangman\Result\HangmanGameResult;
use League\Event\Event;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

abstract class HangmanResultEvent extends Event implements HangmanGameResult
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
     * @var string[]
     */
    private $playedLetters;

    /**
     * @var int
     */
    private $remainingLives;

    /**
     * Constructor
     *
     * @param string     $name
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param string[]   $playedLetters
     * @param int        $remainingLives
     */
    public function __construct($name, MiniGameId $gameId, PlayerId $playerId, array $playedLetters, $remainingLives)
    {
        parent::__construct($name);
        $this->gameId = $gameId;
        $this->playedLetters = $playedLetters;
        $this->playerId = $playerId;
        $this->remainingLives = $remainingLives;
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
        return $this->playedLetters;
    }

    /**
     * @return int
     */
    public function getRemainingLives()
    {
        return $this->remainingLives;
    }

    /**
     * @return string
     */
    abstract public function getAsMessage();
}
