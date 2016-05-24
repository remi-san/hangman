<?php

namespace Hangman\Event;

use Hangman\Event\Util\HangmanErrorEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerTriedPlayingDuringAnotherPlayerTurnEvent extends HangmanErrorEvent
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.wrong-turn';

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     */
    public function __construct(MiniGameId $gameId, PlayerId $playerId)
    {
        parent::__construct(self::NAME, $gameId, $playerId);
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return 'You cannot play.';
    }
}
