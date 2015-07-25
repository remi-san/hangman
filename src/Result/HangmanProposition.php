<?php
namespace Hangman\Result;

use MiniGame\Player;
use MiniGame\Result\Proposition;

abstract class HangmanProposition extends HangmanGameResult implements Proposition
{
    /**
     * @var string
     */
    protected $feedback;

    /**
     * @param Player $player
     * @param string $feedback
     * @param array  $lettersPlayed
     * @param int    $remainingChances
     */
    public function __construct(Player $player, $feedback, array $lettersPlayed, $remainingChances)
    {
        $this->feedback = $feedback;
        parent::__construct($player, $lettersPlayed, $remainingChances);
    }

    /**
     * @return string
     */
    public function getFeedBack()
    {
        return $this->feedback;
    }
}
