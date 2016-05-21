<?php

namespace Hangman\Event\Util;

use League\Event\Event;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\GameResult;

abstract class HangmanBasicResultEvent extends Event implements GameResult
{
    /**
     * @var MiniGameId
     */
    private $gameId;

    /**
     * @var PlayerId
     */
    private $playerId;

    /**
     * Constructor
     *
     * @param string     $name
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     */
    public function __construct($name, MiniGameId $gameId, PlayerId $playerId = null)
    {
        parent::__construct($name);
        $this->gameId = $gameId;
        $this->playerId = $playerId;
    }

    /**
     * @return MiniGameId
     */
    public function getGameId()
    {
        return $this->gameId;
    }

    /**
     * @return PlayerId
     */
    public function getPlayerId()
    {
        return $this->playerId;
    }

    /**
     * @return string
     */
    abstract public function getAsMessage();
}
