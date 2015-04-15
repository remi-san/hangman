<?php
namespace Hangman\Result;

use MiniGame\GameResult;
use MiniGame\Player;
use MiniGame\Result\AbstractGameResult;

abstract class HangmanGameResult extends AbstractGameResult implements GameResult {

    /**
     * @var array
     */
    protected $lettersPlayed;

    /**
     * @var int
     */
    protected $remainingChances;

    /**
     * @param Player $player
     * @param array  $lettersPlayed
     * @param int    $remainingChances
     */
    public function __construct(Player $player, array $lettersPlayed = array(), $remainingChances = null) {
        $this->lettersPlayed = $lettersPlayed;
        $this->remainingChances = $remainingChances;
        parent::__construct($player);
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
    protected function getPlayedLettersAsString() {
        return implode(', ', $this->lettersPlayed);
    }
} 