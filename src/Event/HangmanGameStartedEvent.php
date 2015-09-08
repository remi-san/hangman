<?php
namespace Hangman\Event;

use League\Event\Event;
use MiniGame\Entity\MiniGameId;

class HangmanGameStartedEvent extends Event
{
    /**
     * @var string
     */
    const NAME = 'hangman.started';

    /**
     * @var MiniGameId
     */
    private $gameId;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     */
    public function __construct(MiniGameId $gameId)
    {
        parent::__construct(self::NAME);
        $this->gameId = $gameId;
    }

    /**
     * @return MiniGameId
     */
    public function getGameId()
    {
        return $this->gameId;
    }
}
