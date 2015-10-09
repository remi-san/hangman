<?php
namespace Hangman\Options;

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
     * Constructor
     *
     * @param PlayerId $playerId
     * @param string $name
     * @param $lives
     */
    public function __construct(PlayerId $playerId, $name, $lives)
    {
        parent::__construct($playerId, $name);
        $this->lives = $lives;
    }

    /**
     * @return int
     */
    public function getLives()
    {
        return $this->lives;
    }
}
