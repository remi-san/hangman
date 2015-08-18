<?php
namespace Hangman\Event;

use MiniGame\Entity\MiniGameId;

class HangmanGameCreatedEvent
{
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
