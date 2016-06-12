<?php

namespace Hangman\Event;

use Hangman\Event\Util\HangmanResultEvent;
use Hangman\Result\HangmanWon;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\Result\AllPlayersResult;

class HangmanPlayerWinEvent extends HangmanResultEvent implements AllPlayersResult, HangmanWon
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.win';

    /**
     * @var string
     */
    private $word;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param array      $playedLetters
     * @param int        $remainingLives
     * @param string     $word
     */
    public function __construct(
        MiniGameId $gameId,
        PlayerId $playerId,
        array $playedLetters,
        $remainingLives,
        $word
    ) {
        parent::__construct(self::NAME, $gameId, $playerId, $playedLetters, $remainingLives);
        $this->word = $word;
    }

    /**
     * @return string
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * @return string
     */
    public function getSolution()
    {
        return $this->word;
    }
}
