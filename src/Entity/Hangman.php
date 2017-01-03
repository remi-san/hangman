<?php

namespace Hangman\Entity;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Hangman\Event\HangmanGameCreatedEvent;
use Hangman\Event\HangmanGameFailedStartingEvent;
use Hangman\Event\HangmanGameLostEvent;
use Hangman\Event\HangmanGameStartedEvent;
use Hangman\Event\HangmanPlayerCreatedEvent;
use Hangman\Event\HangmanPlayerFailedCreatingEvent;
use Hangman\Event\HangmanPlayerLostEvent;
use Hangman\Event\HangmanPlayerProposedInvalidAnswerEvent;
use Hangman\Event\HangmanPlayerTriedPlayingDuringAnotherPlayerTurnEvent;
use Hangman\Event\HangmanPlayerTriedPlayingInactiveGameEvent;
use Hangman\Event\HangmanPlayerTurnEvent;
use Hangman\Exception\HangmanException;
use Hangman\Exception\HangmanPlayerOptionsException;
use Hangman\Move\Answer;
use Hangman\Move\Proposition;
use Hangman\Options\HangmanPlayerOptions;
use Hangman\PlayersCollection;
use Hangman\Result\HangmanBadProposition;
use Hangman\Result\HangmanGoodProposition;
use Hangman\Result\HangmanLost;
use Hangman\Result\HangmanWon;
use Hangman\Word;
use MiniGame\Entity\MiniGame;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\Player;
use MiniGame\Entity\PlayerId;
use MiniGame\Entity\PlayTrait;
use MiniGame\GameResult;
use MiniGame\PlayerOptions;

class Hangman extends EventSourcedAggregateRoot implements MiniGame
{
    use PlayTrait;

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////   CONSTANTS   ///////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    const STATE_UNINITIALIZED = 'uninitialized';
    const STATE_READY = 'ready';
    const STATE_STARTED = 'started';
    const STATE_OVER = 'over';

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////   PROPERTIES   ///////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @var MiniGameId
     */
    private $id;

    /**
     * @var Word
     */
    private $word;

    /**
     * @var PlayersCollection
     **/
    private $players;

    /**
     * @var string
     */
    private $state;

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////   PRIVATE CONSTRUCTOR   //////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->state = self::STATE_UNINITIALIZED;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////   ACCESSORS   ///////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Returns the id of the game
     *
     * @return MiniGameId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the aggregate id
     *
     * @return MiniGameId
     */
    public function getAggregateRootId()
    {
        return $this->id;
    }

    /**
     * Returns the name of the mini-game
     *
     * @return string
     */
    public static function getName()
    {
        return 'HANGMAN';
    }

    /**
     * Get the player identified by PlayerId
     *
     * @param PlayerId $playerId
     *
     * @return HangmanPlayer
     */
    public function getPlayer(PlayerId $playerId = null)
    {
        if ($playerId === null) {
            return null;
        }

        return $this->players->get((string)$playerId);
    }

    /**
     * Returns the player who can play
     *
     * @return Player
     */
    public function getCurrentPlayer()
    {
        return $this->players->getCurrentPlayer();
    }

    /**
     * Get the players
     *
     * @return Player[]
     */
    public function getPlayers()
    {
        return $this->players->toArray();
    }

    /**
     * Is game started?
     *
     * @return bool
     */
    public function isGameStarted()
    {
        return $this->state === self::STATE_STARTED;
    }

    /**
     * Is it the player's turn?
     *
     * @param PlayerId $playerId
     *
     * @return bool
     */
    public function canPlayerPlay(PlayerId $playerId)
    {
        return $this->players->canPlay($playerId);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////   DOMAIN METHODS   /////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Starts the game
     *
     * @param PlayerId $playerId
     *
     * @return GameResult
     */
    public function startGame(PlayerId $playerId)
    {
        if (! $this->isGameReady()) {
            return $this->failStarting($playerId, HangmanGameFailedStartingEvent::BAD_STATE);
        }

        if (! $this->players->hasPlayers()) {
            return $this->failStarting($playerId, HangmanGameFailedStartingEvent::NO_PLAYER);
        }

        $event = new HangmanGameStartedEvent($this->id, $playerId);
        $this->apply($event);

        $this->setNextPlayer($playerId);

        return $event;
    }

    /**
     * Adds a player to the game
     *
     * @param PlayerOptions $playerOptions
     *
     * @throws HangmanPlayerOptionsException
     * @throws HangmanException
     *
     * @return GameResult
     */
    public function addPlayerToGame(PlayerOptions $playerOptions)
    {
        if (! $playerOptions instanceof HangmanPlayerOptions) {
            throw new HangmanPlayerOptionsException(
                $playerOptions->getPlayerId(),
                $this->getId(),
                'Options are not recognized'
            );
        }

        if (! $this->isGameReady()) {
            $event = new HangmanPlayerFailedCreatingEvent(
                $this->id,
                $playerOptions->getPlayerId(),
                $playerOptions->getExternalReference()
            );
            $this->apply($event);
            return $event;
        }

        $event = new HangmanPlayerCreatedEvent(
            $this->id,
            $playerOptions->getPlayerId(),
            $playerOptions->getName(),
            $playerOptions->getLives(),
            $playerOptions->getExternalReference()
        );
        $this->apply($event);
        return $event;
    }

    /**
     * A player leaves the game
     *
     * @param PlayerId $playerId
     *
     * @return GameResult
     */
    public function leaveGame(PlayerId $playerId)
    {
        switch ($this->state) {
            case self::STATE_STARTED:
                $player = $this->getPlayer($playerId);
                return $player ? $this->playerLoses($player) : null;
            case self::STATE_OVER:
                break;
            default:
                $this->players->remove((string) $playerId);
                break;
        }
        return null;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////   PRIVATE METHODS   /////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Initialize the game
     *
     * @param MiniGameId             $id
     * @param string                 $word
     */
    private function initialize(MiniGameId $id, $word)
    {
        $this->apply(new HangmanGameCreatedEvent($id, $word));
    }

    /**
     * @return bool
     */
    private function isGameReady()
    {
        return $this->state === self::STATE_READY;
    }

    /**
     * @param PlayerId $playerId
     * @param string   $reason
     *
     * @return HangmanGameFailedStartingEvent
     */
    private function failStarting(PlayerId $playerId, $reason)
    {
        $event = new HangmanGameFailedStartingEvent(
            $this->id,
            $playerId,
            $reason
        );
        $this->apply($event);
        return $event;
    }

    /**
     * Player proposes a letter
     *
     * @param PlayerId $playerId
     * @param Proposition $move
     *
     * @return GameResult
     */
    private function playProposition(PlayerId $playerId, Proposition $move)
    {
        if ($errorEvent = $this->ensurePlayerCanPlay($playerId)) {
            $this->apply($errorEvent);
            return $errorEvent;
        }

        return $this->currentPlayerProposeLetter($move->getText());
    }

    /**
     * Player tries an answer
     *
     * @param PlayerId $playerId
     * @param Answer $move
     *
     * @return GameResult
     */
    private function playAnswer(PlayerId $playerId, Answer $move)
    {
        if ($errorEvent = $this->ensurePlayerCanPlay($playerId)) {
            $this->apply($errorEvent);
            return $errorEvent;
        }

        try {
            return $this->currentPlayerProposeAnswer($move->getText());
        } catch (HangmanException $e) {
            $event = new HangmanPlayerProposedInvalidAnswerEvent(
                $this->getId(),
                $playerId,
                $move
            );
            $this->apply($event);
            return $event;
        }
    }

    /**
     * Returns an error event if player cannot play
     *
     * @param PlayerId $playerId
     *
     * @return GameResult
     */
    private function ensurePlayerCanPlay(PlayerId $playerId)
    {
        if (!$this->isGameStarted()) {
            $event = new HangmanPlayerTriedPlayingInactiveGameEvent(
                $this->getId(),
                $playerId
            );
            return $event;
        }

        if (!$this->canPlayerPlay($playerId)) {
            $event = new HangmanPlayerTriedPlayingDuringAnotherPlayerTurnEvent(
                $this->getId(),
                $playerId
            );
            return $event;
        }

        return null;
    }

    /**
     * Propose a letter
     *
     * @param string $letter
     *
     * @return HangmanBadProposition | HangmanGoodProposition
     */
    private function currentPlayerProposeLetter($letter)
    {
        $result =  (!$this->word->contains($letter))
                   ? $this->currentPlayerBadProposition($letter) // remove a life
                   : $this->currentPlayerGoodProposition($letter); // yay!

        return $result;
    }

    /**
     * Propose an answer
     *
     * @param string $answer
     *
     * @return HangmanLost | HangmanWon
     */
    private function currentPlayerProposeAnswer($answer)
    {
        $this->checkAnswerIsValid($answer);

        if (! $this->word->equals($answer)) {
            return $this->playerLoses($this->players->getCurrentPlayer()); // you lose
        }

        return $this->playerWins($this->players->getCurrentPlayer()); // you win
    }

    /**
     * Function to call when a bad proposition has been made
     *
     * @param string $letter
     *
     * @return HangmanBadProposition | HangmanLost
     */
    private function currentPlayerBadProposition($letter)
    {
        $player = $this->players->getCurrentPlayer();

        $event = $player->playBadLetter($letter, 1);

        if ($event->getRemainingLives() === 0) {
            return $this->playerLoses($player);
        }

        $this->setNextPlayer($this->players->getNextPlayerId());

        return $event;
    }

    /**
     * Function to call after a good proposition of letter has been made
     *
     * @param string $letter
     *
     * @return HangmanGoodProposition | HangmanWon
     */
    private function currentPlayerGoodProposition($letter)
    {
        $player = $this->players->getCurrentPlayer();

        $event = $player->playGoodLetter($letter);

        if ($this->isAllLettersFoundForPlayer($player)) {
            return $this->playerWins($player);
        }

        $this->setNextPlayer($this->players->getNextPlayerId());

        return $event;
    }

    /**
     * Function to call when game is won by a player
     *
     * @param HangmanPlayer $player
     *
     * @return HangmanWon
     */
    private function playerWins(HangmanPlayer $player)
    {
        $event = $player->win($this->word);

        foreach ($this->players as $otherPlayer) {
            if ($otherPlayer->equals($player) || $otherPlayer->hasLost()) {
                continue;
            }
            $otherPlayer->lose($this->word);
        }

        return $event;
    }

    /**
     * Function to call when game is lost by a player
     *
     * @param HangmanPlayer $player
     *
     * @return hangmanLost | HangmanGameLostEvent
     */
    private function playerLoses(HangmanPlayer $player)
    {
        $event = $player->lose($this->word);

        if ($this->players->hasAtLeastOneActivePlayer() &&
            $this->players->isCurrentPlayer($player->getId())
        ) {
            $this->setNextPlayer($this->players->getNextPlayerId());
            return $event;
        }

        $event = new HangmanGameLostEvent(
            $this->id,
            $player->getId(),
            (string) $this->word
        );
        $this->apply($event);

        return $event;
    }

    /**
     * Sets the next player
     *
     * @param PlayerId $id
     */
    private function setNextPlayer(PlayerId $id = null)
    {
        if ($id === null || $this->players->isCurrentPlayer($id)) {
            return;
        }

        $this->apply(
            new HangmanPlayerTurnEvent($this->getId(), $id)
        );
    }

    /**
     * Build the word from played letters
     *
     * @param string[] $playedLetters
     *
     * @return string
     */
    public function buildWord($playedLetters)
    {
        return $this->word->buildWord($playedLetters);
    }

    /**
     * Checks if all letters for the word have been found
     *
     * @param HangmanPlayer $player
     *
     * @return bool
     */
    private function isAllLettersFoundForPlayer(HangmanPlayer $player)
    {
        $wordLetters = $this->word->getLetters();
        $playerLetters = $player->getPlayedLetters();
        return count(array_intersect($wordLetters, $playerLetters)) == count($wordLetters);
    }

    /**
     * Checks if the answer is valid
     * If it's not, ends player turn and throws an HangmanException
     *
     * @param string $answer
     *
     * @throws HangmanException
     */
    private function checkAnswerIsValid($answer)
    {
        if (! $this->word->isValid($answer)) {
            throw new HangmanException(sprintf('"%s" is not a valid answer!', $answer));
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////   APPLY EVENTS   //////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Apply the game created event
     *
     * @param HangmanGameCreatedEvent $event
     *
     * @return void
     */
    protected function applyHangmanGameCreatedEvent(HangmanGameCreatedEvent $event)
    {
        $this->id = $event->getGameId();
        $this->word = new Word($event->getWord());
        $this->players = new PlayersCollection();
        $this->state = self::STATE_READY;
    }

    /**
     * Apply the player created event
     *
     * @param HangmanPlayerCreatedEvent $event
     *
     * @return void
     */
    protected function applyHangmanPlayerCreatedEvent(HangmanPlayerCreatedEvent $event)
    {
        $this->players->add(
            new HangmanPlayer(
                $event->getPlayerId(),
                $event->getPlayerName(),
                $event->getLives(),
                $this,
                $event->getExternalReference()
            )
        );
    }

    /**
     * Apply the game created event
     */
    protected function applyHangmanGameStartedEvent()
    {
        $this->state = self::STATE_STARTED;
    }

    /**
     * Apply the player turn event
     *
     * @param HangmanPlayerTurnEvent $event
     */
    protected function applyHangmanPlayerTurnEvent(HangmanPlayerTurnEvent $event)
    {
        $this->players->setCurrentPlayer($event->getPlayerId());
    }

    /**
     * Apply the hangman player lost event
     *
     * @param HangmanPlayerLostEvent $event
     */
    protected function applyHangmanPlayerLostEvent(HangmanPlayerLostEvent $event)
    {
        $this->state = self::STATE_OVER;
    }

    /**
     * Apply the hangman player win event
     *
     * @return void
     */
    protected function applyHangmanPlayerWinEvent()
    {
        $this->players->setCurrentPlayer(null);
        $this->state = self::STATE_OVER;
    }

    /**
     * Apply the hangman lost by all event
     *
     * @return void
     */
    protected function applyHangmanGameLostEvent()
    {
        $this->players->setCurrentPlayer(null);
        $this->state = self::STATE_OVER;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////   EVENT SOURCED   /////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return Player[]
     */
    protected function getChildEntities()
    {
        return $this->getPlayers();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////   STATIC CONSTRUCTOR   ///////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Create a new instance
     *
     * @param MiniGameId $id
     * @param string     $word
     *
     * @return Hangman
     */
    public static function createGame(MiniGameId $id, $word)
    {
        $hangman = new self();
        $hangman->initialize($id, $word);

        return $hangman;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////   RECONSTITUTION   /////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Static construction method for reconstitution
     *
     * @return Hangman
     */
    public static function instantiateForReconstitution()
    {
        return new self();
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////   APPLY RESTRICTIONS   ///////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @param mixed $event
     *
     * @throws HangmanException
     */
    public function apply($event)
    {
        if (! $this->isSupportedEvent($event)) {
            throw new HangmanException('You cannot apply a non hangman event.');
        }

        parent::apply($event);
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
            ($this->id === null || $this->id == $event->getGameId())
        );
    }
}
