<?php

namespace Hangman\Event;

use Hangman\Event\Util\HangmanResultEvent;
use Hangman\Result\HangmanGoodProposition;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanGoodLetterProposedEvent extends HangmanResultEvent implements HangmanGoodProposition
{
    /**
     * @var string
     */
    const NAME = 'hangman.letter.good';

    /**
     * @var string
     */
    private $letter;

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
     * @param int        $remainingLives
     * @param string     $wordSoFar
     */
    public function __construct(
        MiniGameId $gameId,
        PlayerId $playerId,
        $letter,
        array $playedLetters,
        $remainingLives,
        $wordSoFar
    ) {
        parent::__construct(self::NAME, $gameId, $playerId, $playedLetters, $remainingLives);
        $this->letter = $letter;
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
     * @return string
     */
    public function getWordSoFar()
    {
        return $this->wordSoFar;
    }

    /**
     * @return string
     */
    public function getFeedBack()
    {
        return $this->wordSoFar;
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return sprintf(
            'Well played! %s (letters played: %s) - Remaining chances: %d',
            $this->getFeedBack(),
            implode(', ', $this->getPlayedLetters()),
            $this->getRemainingLives()
        );
    }
}
