<?php
namespace Hangman\Result;

use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\GameResult;
use MiniGame\Result\AbstractGameResult;

abstract class HangmanGameResult extends AbstractGameResult implements GameResult
{
    /**
     * @var array
     */
    protected $lettersPlayed;

    /**
     * @var int
     */
    protected $remainingChances;

    /**
     * Constructor
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param array      $lettersPlayed
     * @param int        $remainingChances
     */
    public function __construct(
        MiniGameId $gameId,
        PlayerId $playerId,
        array $lettersPlayed = array(),
        $remainingChances = null
    ) {
        $this->lettersPlayed = $lettersPlayed;
        $this->remainingChances = $remainingChances;
        parent::__construct($gameId, $playerId);
    }

    /**
     * @return array
     */
    public function getLettersPlayed()
    {
        return $this->lettersPlayed;
    }

    /**
     * @return int
     */
    public function getRemainingChances()
    {
        return $this->remainingChances;
    }

    /**
     * @return string
     */
    protected function getPlayedLettersAsString()
    {
        return implode(', ', $this->lettersPlayed);
    }
}
