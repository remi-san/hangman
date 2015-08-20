<?php
namespace Hangman\Event;

use League\Event\Event;
use MiniGame\Entity\MiniGameId;

class HangmanGameCreatedEvent extends Event
{
    /**
     * @var string
     */
    const NAME = 'game.created';

    /**
     * @var MiniGameId
     */
    private $gameId;

    /**
     * @var string
     */
    private $word;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param string     $word
     */
    public function __construct(MiniGameId $gameId, $word)
    {
        parent::__construct(self::NAME);
        $this->gameId = $gameId;
        $this->word = $word;
    }

    /**
     * @return MiniGameId
     */
    public function getGameId()
    {
        return $this->gameId;
    }

    /**
     * @return string
     */
    public function getWord()
    {
        return $this->word;
    }
}
