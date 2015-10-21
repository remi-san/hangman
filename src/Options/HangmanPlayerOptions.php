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
     * Constructor
     *
     * @param PlayerId   $playerId
     * @param MiniGameId $gameId
     * @param string     $name
     * @param string     $lives
     * @param string     $externalReference
     */
    public function __construct(PlayerId $playerId, MiniGameId $gameId, $name, $lives, $externalReference = null)
    {
        parent::__construct($playerId, $gameId, $name);
        $this->lives = $lives;
        $this->externalReference = $externalReference;
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
}
