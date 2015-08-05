<?php
namespace Hangman\Result;

use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\Result\MoveResult;

abstract class HangmanProposition extends HangmanGameResult implements MoveResult
{
    /**
     * @var string
     */
    protected $feedback;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $player
     * @param string     $feedback
     * @param array      $lettersPlayed
     * @param int        $remainingChances
     */
    public function __construct(
        MiniGameId $gameId,
        PlayerId $player,
        $feedback,
        array $lettersPlayed,
        $remainingChances
    ) {
        $this->feedback = $feedback;
        parent::__construct($gameId, $player, $lettersPlayed, $remainingChances);
    }

    /**
     * @return string
     */
    public function getFeedBack()
    {
        return $this->feedback;
    }
}
