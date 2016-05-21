<?php

namespace Hangman\Exception;

use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerOptionsException extends \Exception
{
    /**
     * @var PlayerId
     */
    private $playerId;

    /**
     * @var MiniGameId
     */
    private $miniGameId;

    /**
     * Constructor
     *
     * @param PlayerId   $playerId
     * @param MiniGameId $miniGameId
     * @param string     $message
     * @param int        $code
     * @param \Exception $previous
     */
    public function __construct(
        PlayerId $playerId,
        MiniGameId $miniGameId,
        $message = "",
        $code = 0,
        \Exception $previous = null
    ) {
        $this->playerId = $playerId;
        $this->miniGameId = $miniGameId;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return PlayerId
     */
    public function getPlayerId()
    {
        return $this->playerId;
    }

    /**
     * @return MiniGameId
     */
    public function getMiniGameId()
    {
        return $this->miniGameId;
    }
}
