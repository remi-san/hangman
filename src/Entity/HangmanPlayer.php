<?php

namespace Hangman\Entity;

use Broadway\EventSourcing\EventSourcedEntity;
use Hangman\Event\HangmanBadLetterProposedEvent;
use Hangman\Event\HangmanGoodLetterProposedEvent;
use Hangman\Event\HangmanPlayerLostEvent;
use Hangman\Event\HangmanPlayerWinEvent;
use Hangman\Word;
use MiniGame\Entity\MiniGame;
use MiniGame\Entity\Player;
use MiniGame\Entity\PlayerId;
use MiniGame\GameResult;
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

    const DEFAULT_LIVES = 6;

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
        $lives = self::DEFAULT_LIVES,
        Hangman $game = null,
        $externalReference = null
    ) {
        $this->id = ($id !== null) ? $id : PlayerId::create(Uuid::uuid4()->toString());
        $this->name = $name;
        $this->lives = $lives;
        $this->playedLetters = [];
        $this->game = $game;
        $this->externalReference = $externalReference;
        $this->state = self::STATE_IN_GAME;

        if ($game !== null) {
            $this->registerAggregateRoot($game);
        }
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
        return array_values($this->playedLetters);
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

    /**
     * @return bool
     */
    public function hasLost()
    {
        return $this->state === self::STATE_LOST;
    }

    /**
     * @return bool
     */
    public function hasWon()
    {
        return $this->state === self::STATE_WON;
    }

    /**
     * @param  Player $player
     * @return bool
     */
    public function equals(Player $player = null)
    {
        return $player instanceof HangmanPlayer && $this->id->equals($player->id);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////   DOMAIN METHODS   /////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param string $letter
     * @param int    $livesLost
     *
     * @return HangmanBadLetterProposedEvent
     */
    public function playBadLetter($letter, $livesLost)
    {
        $playedLetters = $this->getPlayedLetters();
        $playedLetters[] = strtoupper($letter);

        $event = new HangmanBadLetterProposedEvent(
            $this->game->getId(),
            $this->id,
            strtoupper($letter),
            $playedLetters,
            $livesLost,
            $this->getRemainingLives()-$livesLost,
            $this->game->buildWord($playedLetters)
        );

        $this->apply($event);

        return $event;
    }

    /**
     * @param string $letter
     *
     * @return HangmanGoodLetterProposedEvent
     */
    public function playGoodLetter($letter)
    {
        $playedLetters = $this->getPlayedLetters();
        $playedLetters[] = strtoupper($letter);

        $event = new HangmanGoodLetterProposedEvent(
            $this->game->getId(),
            $this->id,
            strtoupper($letter),
            $playedLetters,
            $this->getRemainingLives(),
            $this->game->buildWord($playedLetters)
        );

        $this->apply($event);

        return $event;
    }

    /**
     * @param Word $word
     *
     * @return HangmanPlayerWinEvent
     */
    public function win(Word $word)
    {
        $event = new HangmanPlayerWinEvent(
            $this->game->getId(),
            $this->getId(),
            $this->getPlayedLetters(),
            $this->getRemainingLives(),
            (string) $word
        );
        $this->apply($event);

        return $event;
    }

    /**
     * @param Word $word
     *
     * @return HangmanPlayerLostEvent
     */
    public function lose(Word $word)
    {
        $playedLetters = $this->getPlayedLetters();

        $event = new HangmanPlayerLostEvent(
            $this->game->getId(),
            $this->getId(),
            $playedLetters,
            $this->getRemainingLives(),
            $this->game->buildWord($playedLetters),
            (string) $word
        );
        $this->apply($event);

        return $event;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////   UTIL METHODS   //////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Players played a letter
     *
     * @param string $letter
     */
    private function playedLetter($letter)
    {
        $this->playedLetters[strtoupper($letter)] = strtoupper($letter);
    }

    /**
     * Player loses a life
     *
     * @param int $nbLives
     */
    private function lifeLost($nbLives = 1)
    {
        $this->lives -= $nbLives;
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
        $this->lifeLost($event->getLivesLost());
        $this->playedLetter($event->getLetter());
    }

    /**
     * Apply the bad letter played event
     *
     * @param  HangmanGoodLetterProposedEvent $event
     * @return void
     */
    protected function applyHangmanGoodLetterProposedEvent(HangmanGoodLetterProposedEvent $event)
    {
        $this->playedLetter($event->getLetter());
    }

    /**
     * Apply the hangman player lost event
     *
     * @param HangmanPlayerLostEvent $event
     */
    protected function applyHangmanPlayerLostEvent(HangmanPlayerLostEvent $event)
    {
        $this->state = self::STATE_LOST;
    }

    /**
     * Apply the hangman player win event
     *
     * @param HangmanPlayerWinEvent $event
     */
    protected function applyHangmanPlayerWinEvent(HangmanPlayerWinEvent $event)
    {
        $this->state = self::STATE_WON;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////   APPLY RESTRICTIONS   ///////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    /**
     * @param mixed $event
     */
    public function handleRecursively($event)
    {
        if (! $this->isSupportedEvent($event)) {
            return;
        }

        parent::handleRecursively($event);
    }

    /**
     * @param mixed $event
     *
     * @return bool
     */
    private function isSupportedEvent($event)
    {
        return (
            $event instanceof GameResult &&
            $this->id == $event->getPlayerId() &&
            $this->game->getId() == $event->getGameId()
        );
    }
}
