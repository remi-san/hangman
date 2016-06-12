<?php

namespace Hangman\Event;

use Hangman\Event\Util\HangmanResultEvent;
use Hangman\Result\HangmanLost;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerLostEvent extends HangmanResultEvent implements HangmanLost
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.lost';

    /**
     * @var string
     */
    private $wordFound;

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
     * @param string     $wordFound
     * @param string     $word
     */
    public function __construct(
        MiniGameId $gameId,
        PlayerId $playerId,
        array $playedLetters,
        $remainingLives,
        $wordFound,
        $word
    ) {
        parent::__construct(self::NAME, $gameId, $playerId, $playedLetters, $remainingLives);
        $this->wordFound = $wordFound;
        $this->word = $word;
    }

    /**
     * @return string
     */
    public function getWordFound()
    {
        return $this->wordFound;
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
