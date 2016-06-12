<?php

namespace Hangman\Event;

use Hangman\Event\Util\HangmanBasicResultEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\Result\AllPlayersResult;
use MiniGame\Result\GameLost;

class HangmanGameLostEvent extends HangmanBasicResultEvent implements AllPlayersResult, GameLost
{
    /**
     * @var string
     */
    const NAME = 'hangman.lost';

    /**
     * @var string
     */
    private $word;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param string     $word
     */
    public function __construct(MiniGameId $gameId, PlayerId $playerId, $word)
    {
        parent::__construct(self::NAME, $gameId, $playerId);
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
    public function getSolution()
    {
        return $this->word;
    }
}
