<?php

namespace Hangman\Event;

use Hangman\Event\Util\HangmanBasicResultEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\Event\PlayerCreatedEvent;

class HangmanPlayerCreatedEvent extends HangmanBasicResultEvent implements PlayerCreatedEvent
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.created';

    /**
     * @var string
     */
    private $playerName;

    /**
     * @var int
     */
    private $lives;

    /**
     * @var string
     */
    private $externalReference;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param string     $playerName
     * @param int        $lives
     * @param string     $externalReference
     */
    public function __construct(MiniGameId $gameId, PlayerId $playerId, $playerName, $lives, $externalReference)
    {
        parent::__construct(self::NAME, $gameId, $playerId);
        $this->playerName = $playerName;
        $this->lives = $lives;
        $this->externalReference = $externalReference;
    }

    /**
     * @return string
     */
    public function getPlayerName()
    {
        return $this->playerName;
    }

    /**
     * @return int
     */
    public function getLives()
    {
        return $this->lives;
    }

    /**
     * @return string
     */
    public function getExternalReference()
    {
        return $this->externalReference;
    }
}
