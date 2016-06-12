<?php

namespace Hangman\Event;

use Hangman\Event\Util\HangmanErrorEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerTriedPlayingInactiveGameEvent extends HangmanErrorEvent
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.inactive-game';

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
}
