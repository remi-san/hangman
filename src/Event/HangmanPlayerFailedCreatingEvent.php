<?php

namespace Hangman\Event;

use Hangman\Event\Util\HangmanErrorEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerFailedCreatingEvent extends HangmanErrorEvent
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.failed-creating';

    /**
     * @var string
     */
    private $externalReference;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param string     $externalReference
     */
    public function __construct(MiniGameId $gameId, PlayerId $playerId, $externalReference)
    {
        parent::__construct(self::NAME, $gameId, $playerId);
        $this->externalReference = $externalReference;
    }

    /**
     * @return string
     */
    public function getExternalReference()
    {
        return $this->externalReference;
    }
}
