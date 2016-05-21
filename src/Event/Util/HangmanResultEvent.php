<?php

namespace Hangman\Event\Util;

use Hangman\Result\HangmanGameResult;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

abstract class HangmanResultEvent extends HangmanBasicResultEvent implements HangmanGameResult
{
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
        parent::__construct($name, $gameId, $playerId);
        $this->playedLetters = $playedLetters;
        $this->remainingLives = $remainingLives;
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
}
