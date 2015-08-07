<?php
namespace Hangman\Entity;

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
     * @var int
     */
    protected $lives;

    /**
     * @var string[]
     */
    protected $playedLetters;

    /**
     * @var MiniGame
     */
    protected $game;

    /**
     * Constructor
     *
     * @param PlayerId $id
     * @param string   $name
     * @param int      $lives
     * @param MiniGame $game
     */
    public function __construct(PlayerId $id = null, $name = null, $lives = 6, MiniGame $game = null)
    {
        $this->id = ($id !== null) ? $id : new PlayerId(Uuid::uuid4()->toString());
        $this->name = $name;
        $this->lives = $lives;
        $this->playedLetters = array();
        $this->game = $game;
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
     * Gets the number of lives remaining
     *
     * @return int
     */
    public function getRemainingLives()
    {
        return $this->lives;
    }

    /**
     * Gets the played letters
     *
     * @return string[]
     */
    public function getPlayedLetters()
    {
        return $this->playedLetters;
    }

    /**
     * Player loses a life
     */
    public function loseLife()
    {
        $this->lives--;
    }

    /**
     * Players played a letter
     *
     * @param string $letter
     */
    public function playLetter($letter)
    {
        $this->playedLetters[strtoupper($letter)] = strtoupper($letter);
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
