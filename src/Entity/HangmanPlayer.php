<?php
namespace Hangman\Entity;

use Broadway\EventSourcing\EventSourcedEntity;
use Hangman\Event\HangmanBadLetterProposedEvent;
use MiniGame\Entity\MiniGame;
use MiniGame\Entity\Player;
use MiniGame\Entity\PlayerId;
use Rhumsaa\Uuid\Uuid;

class HangmanPlayer extends EventSourcedEntity implements Player
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
     * @var Hangman
     */
    protected $game;

    /**
     * @var string
     */
    private $externalReference;

    /**
     * Constructor
     *
     * @param PlayerId $id
     * @param string   $name
     * @param int      $lives
     * @param Hangman  $game
     * @param string   $externalReference
     */
    public function __construct(
        PlayerId $id = null,
        $name = null,
        $lives = 6,
        Hangman $game = null,
        $externalReference = null
    ) {
        $this->id = ($id !== null) ? $id : new PlayerId(Uuid::uuid4()->toString());
        $this->name = $name;
        $this->lives = $lives;
        $this->playedLetters = array();
        $this->game = $game;
        $this->externalReference = $externalReference;
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
     * Gets the external reference
     *
     * @return string
     */
    public function getExternalReference()
    {
        return $this->externalReference;
    }

    /**
     * Player loses a life
     *
     * @param int $nbLives
     */
    public function loseLife($nbLives = 1)
    {
        $this->lives -= $nbLives;
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

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////   APPLY EVENTS   //////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Apply the bad letter played event
     *
     * @param  HangmanBadLetterProposedEvent $event
     * @return void
     */
    protected function applyHangmanBadLetterProposedEvent(HangmanBadLetterProposedEvent $event)
    {
        if ((string)$event->getPlayerId() === (string)$this->getId()) {
            $this->loseLife($event->getLivesLost());
        }
    }
}
