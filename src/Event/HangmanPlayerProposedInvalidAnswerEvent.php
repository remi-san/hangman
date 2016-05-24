<?php

namespace Hangman\Event;

use Hangman\Event\Util\HangmanErrorEvent;
use Hangman\Move\Answer;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\Move;

class HangmanPlayerProposedInvalidAnswerEvent extends HangmanErrorEvent
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.invalid-answer';

    /**
     * @var Answer
     */
    private $answer;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param Answer     $answer
     */
    public function __construct(MiniGameId $gameId, PlayerId $playerId, Answer $answer)
    {
        parent::__construct(self::NAME, $gameId, $playerId);
        $this->answer = $answer;
    }

    /**
     * @return Move
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return 'Invalid answer';
    }
}
