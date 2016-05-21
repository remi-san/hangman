<?php

namespace Hangman\Options;

use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\Options\AbstractPlayerOptions;
use MiniGame\PlayerOptions;

class HangmanPlayerOptions extends AbstractPlayerOptions implements PlayerOptions
{
    /**
     * @var int
     */
    private $lives;

    /**
     * @var string
     */
    private $externalReference;

    /**
     * Constructor.
     */
    public function __construct()
    {
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

    /**
     * Constructor
     *
     * @param PlayerId   $playerId
     * @param MiniGameId $gameId
     * @param string     $name
     * @param int        $lives
     * @param string     $externalReference
     *
     * @return HangmanPlayerOptions
     */
    public static function create(PlayerId $playerId, MiniGameId $gameId, $name, $lives, $externalReference = null)
    {
        $obj = new self();

        $obj->init($playerId, $gameId, $name);
        $obj->lives = $lives;
        $obj->externalReference = $externalReference;

        return $obj;
    }
}
