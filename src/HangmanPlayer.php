<?php
namespace Hangman;

use MiniGame\Entity\MiniGame;
use MiniGame\Entity\Player;
use MiniGame\Entity\PlayerId;
use Rhumsaa\Uuid\Uuid;

class HangmanPlayer implements Player
{
    /**
     * @var PlayerId
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var MiniGame
     */
    protected $game;

    /**
     * Constructor
     *
     * @param PlayerId $id
     * @param string   $name
     * @param MiniGame $game
     */
    public function __construct($id = null, $name = null, MiniGame $game = null)
    {
        $this->id = ($id !== null) ? $id : new PlayerId(Uuid::uuid4()->toString());
        $this->name = $name;
    }

    /**
     * Returns the id of the player
     *
     * @return PlayerId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the name of the player
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the game
     *
     * @return MiniGame
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * Sets the game
     *
     * @param  MiniGame $game
     * @return void
     */
    public function setGame(MiniGame $game)
    {
        $this->game = $game;
    }
}
