<?php
namespace Hangman\Result;

use MiniGame\Player;
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
     * @param Player $player
     * @param array  $lettersPlayed
     * @param int    $remainingChances
     * @param string $solution
     */
    public function __construct(Player $player, array $lettersPlayed, $remainingChances, $solution)
    {
        $this->solution = $solution;
        parent::__construct($player, $lettersPlayed, $remainingChances);
    }

    /**
     * @return mixed
     */
    public function getSolution()
    {
        return $this->solution;
    }
}
