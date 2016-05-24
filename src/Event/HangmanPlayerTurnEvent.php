<?php

namespace Hangman\Event;

use Hangman\Event\Util\HangmanBasicResultEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\GameResult;

class HangmanPlayerTurnEvent extends HangmanBasicResultEvent implements GameResult
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.turn';

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     */
    public function __construct(
        MiniGameId $gameId,
        PlayerId $playerId
    ) {
        parent::__construct(self::NAME, $gameId, $playerId);
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return sprintf('It is your turn to play');
    }
}
