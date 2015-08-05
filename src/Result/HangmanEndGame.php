<?php
namespace Hangman\Result;

use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\Result\EndGame;

abstract class HangmanEndGame extends HangmanGameResult implements EndGame
{
    /**
     * @var string
     */
    protected $solution;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $player
     * @param array      $lettersPlayed
     * @param int        $remainingChances
     * @param string     $solution
     */
    public function __construct(
        MiniGameId $gameId,
        PlayerId $player,
        array $lettersPlayed,
        $remainingChances,
        $solution
    ) {
        $this->solution = $solution;
        parent::__construct($gameId, $player, $lettersPlayed, $remainingChances);
    }

    /**
     * @return mixed
     */
    public function getSolution()
    {
        return $this->solution;
    }
}
