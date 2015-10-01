<?php
namespace Hangman\Event;

use League\Event\Event;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerLostEvent extends Event
{
    /**
     * @var string
     */
    const NAME = 'hangman.lost';

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
        parent::__construct(self::NAME);
        $this->gameId = $gameId;
        $this->playerId = $playerId;
        $this->playedLetters = $playedLetters;
        $this->remainingLives = $remainingLives;
        $this->wordFound = $wordFound;
        $this->word = $word;
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
     * @return string[]
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
}
