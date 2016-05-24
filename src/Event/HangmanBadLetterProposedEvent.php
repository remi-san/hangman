<?php

namespace Hangman\Event;

use Hangman\Event\Util\HangmanResultEvent;
use Hangman\Result\HangmanBadProposition;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanBadLetterProposedEvent extends HangmanResultEvent implements HangmanBadProposition
{
    /**
     * @var string
     */
    const NAME = 'hangman.letter.bad';

    /**
     * @var string
     */
    private $letter;

    /**
     * @var int
     */
    private $livesLost;

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
        parent::__construct(self::NAME, $gameId, $playerId, $playedLetters, $remainingLives);
        $this->letter = $letter;
        $this->livesLost = $livesLost;
        $this->wordSoFar = $wordSoFar;
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
     * @return string
     */
    public function getWordSoFar()
    {
        return $this->wordSoFar;
    }

    /**
     * @return string
     */
    public function getFeedback()
    {
        return $this->wordSoFar;
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return sprintf(
            'Too bad... %s (letters played: %s) - Remaining chances: %d',
            $this->getWordSoFar(),
            implode(', ', $this->getPlayedLetters()),
            $this->getRemainingLives()
        );
    }
}
