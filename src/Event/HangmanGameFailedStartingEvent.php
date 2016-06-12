<?php

namespace Hangman\Event;

use Hangman\Event\Util\HangmanErrorEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanGameFailedStartingEvent extends HangmanErrorEvent
{
    /**
     * @var string
     */
    const NAME = 'hangman.starting.failed';

    /**
     * @var string
     */
    const BAD_STATE = 'alreadyStarted';

    /**
     * @var string
     */
    const NO_PLAYER = 'noPlayer';

    /**
     * @var string
     */
    private $reason;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param string     $reason
     */
    public function __construct(MiniGameId $gameId, PlayerId $playerId = null, $reason = '')
    {
        parent::__construct(self::NAME, $gameId, $playerId);
        $this->reason = $reason;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }
}
