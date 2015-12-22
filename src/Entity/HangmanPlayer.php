<?php
namespace Hangman\Entity;

use Broadway\EventSourcing\EventSourcedEntity;
use Hangman\Event\HangmanBadLetterProposedEvent;
use Hangman\Event\HangmanGoodLetterProposedEvent;
use Hangman\Event\HangmanPlayerLostEvent;
use Hangman\Event\HangmanPlayerWinEvent;
use MiniGame\Entity\MiniGame;
use MiniGame\Entity\Player;
use MiniGame\Entity\PlayerId;
use Rhumsaa\Uuid\Uuid;

class HangmanPlayer extends EventSourcedEntity implements Player
{
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////   CONSTANTS   ///////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    const STATE_IN_GAME = 'in-game';
    const STATE_LOST = 'lost';
    const STATE_WON = 'won';

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////   PROPERTIES   ///////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @var PlayerId
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $lives;

    /**
     * @var string[]
     */
    private $playedLetters;

    /**
     * @var Hangman
     */
    private $game;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $externalReference;

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////   PUBLIC CONSTRUCTOR   //////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
        $this->state = self::STATE_IN_GAME;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////   ACCESSORS   ///////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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
     * Gets the state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////   DOMAIN METHODS   /////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

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

    /**
     * @return void
     */
    public function win()
    {
        $this->state = self::STATE_WON;
    }

    /**
     * @return void
     */
    public function lose()
    {
        $this->state = self::STATE_LOST;
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
            $this->playLetter($event->getLetter());
        }
    }

    /**
     * Apply the bad letter played event
     *
     * @param  HangmanGoodLetterProposedEvent $event
     * @return void
     */
    protected function applyHangmanGoodLetterProposedEvent(HangmanGoodLetterProposedEvent $event)
    {
        if ((string)$event->getPlayerId() === (string)$this->getId()) {
            $this->playLetter($event->getLetter());
        }
    }

    /**
     * Apply the hangman player lost event
     *
     * @param HangmanPlayerLostEvent $event
     */
    protected function applyHangmanPlayerLostEvent(HangmanPlayerLostEvent $event)
    {
        if ((string)$event->getPlayerId() === (string)$this->getId()) {
            $this->lose();
        }
    }

    /**
     * Apply the hangman player win event
     *
     * @param HangmanPlayerWinEvent $event
     */
    protected function applyHangmanPlayerWinEvent(HangmanPlayerWinEvent $event)
    {
        if ((string)$event->getPlayerId() === (string)$this->getId()) {
            $this->win();
        }
    }
}
