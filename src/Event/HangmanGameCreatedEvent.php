<?php

namespace Hangman\Event;

use Hangman\Event\Util\HangmanBasicResultEvent;
use MiniGame\Entity\MiniGameId;

class HangmanGameCreatedEvent extends HangmanBasicResultEvent
{
    /**
     * @var string
     */
    const NAME = 'hangman.created';

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
        parent::__construct(self::NAME, $gameId);
        $this->word = $word;
    }

    /**
     * @return string
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return 'Game created';
    }
}
