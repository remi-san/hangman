<?php
namespace Hangman\Event;

use League\Event\Event;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanBadLetterProposedEvent extends Event
{
    /**
     * @var string
     */
    const NAME = 'hangman.letter.bad';

    /**
     * @var MiniGameId
     */
    private $gameId;

    /**
     * @var PlayerId
     */
    private $playerId;

    /**
     * @var string
     */
    private $letter;

    /**
     * @var string[]
     */
    private $playedLetters;

    /**
     * @var int
     */
    private $livesLost;

    /**
     * @var int
     */
    private $remainingLives;

    /**
     * @var string
     */
    private $wordSoFar;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param string     $letter
     * @param array      $playedLetters
     * @param int        $livesLost
     * @param int        $remainingLives
     * @param string     $wordSoFar
     */
    public function __construct(
        MiniGameId $gameId,
        PlayerId $playerId,
        $letter,
        array $playedLetters,
        $livesLost,
        $remainingLives,
        $wordSoFar
    ) {
        parent::__construct(self::NAME);
        $this->gameId = $gameId;
        $this->playerId = $playerId;
        $this->letter = $letter;
        $this->playedLetters = $playedLetters;
        $this->livesLost = $livesLost;
        $this->remainingLives = $remainingLives;
        $this->wordSoFar = $wordSoFar;
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
     * @return string
     */
    public function getLetter()
    {
        return $this->letter;
    }

    /**
     * @return int
     */
    public function getLivesLost()
    {
        return $this->livesLost;
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
    public function getWordSoFar()
    {
        return $this->wordSoFar;
    }
}
