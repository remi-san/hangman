<?php

namespace Hangman\Event;

use Hangman\Event\Util\HangmanBasicResultEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\Result\AllPlayersResult;

class HangmanGameStartedEvent extends HangmanBasicResultEvent implements AllPlayersResult
{
    /**
     * @var string
     */
    const NAME = 'hangman.started';

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     */
    public function __construct(MiniGameId $gameId, PlayerId $playerId = null)
    {
        parent::__construct(self::NAME, $gameId, $playerId);
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return 'Game started';
    }
}
