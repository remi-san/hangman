<?php
namespace Hangman\Event;

use League\Event\Event;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\Player;

class HangmanPlayerCreatedEvent extends Event
{
    /**
     * @var string
     */
    const NAME = 'game.created';

    /**
     * @var Player
     */
    private $player;

    /**
     * @var MiniGameId
     */
    private $gameId;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param Player     $player
     */
    public function __construct(MiniGameId $gameId, Player $player)
    {
        parent::__construct(self::NAME);
        $this->gameId = $gameId;
        $this->player = $player;
    }

    /**
     * @return MiniGameId
     */
    public function getGameId()
    {
        return $this->gameId;
    }

    /**
     * @return Player
     */
    public function getPlayer()
    {
        return $this->player;
    }
}
