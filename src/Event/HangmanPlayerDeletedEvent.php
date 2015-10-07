<?php
namespace Hangman\Event;

use League\Event\Event;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\Player;
use MiniGame\Entity\PlayerId;

class HangmanPlayerDeletedEvent extends Event
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.deleted';

    /**
     * @var PlayerId
     */
    private $playerId;

    /**
     * @var MiniGameId
     */
    private $gameId;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     */
    public function __construct(MiniGameId $gameId, PlayerId $playerId)
    {
        parent::__construct(self::NAME);
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
}
