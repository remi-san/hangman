<?php

namespace Hangman\Entity;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Hangman\Event\HangmanBadLetterProposedEvent;
use Hangman\Event\HangmanGameCreatedEvent;
use Hangman\Event\HangmanGameFailedStartingEvent;
use Hangman\Event\HangmanGameLostEvent;
use Hangman\Event\HangmanGameStartedEvent;
use Hangman\Event\HangmanGoodLetterProposedEvent;
use Hangman\Event\HangmanPlayerCreatedEvent;
use Hangman\Event\HangmanPlayerFailedCreatingEvent;
use Hangman\Event\HangmanPlayerLostEvent;
use Hangman\Event\HangmanPlayerProposedInvalidAnswerEvent;
use Hangman\Event\HangmanPlayerTriedPlayingDuringAnotherPlayerTurnEvent;
use Hangman\Event\HangmanPlayerTriedPlayingInactiveGameEvent;
use Hangman\Event\HangmanPlayerTurnEvent;
use Hangman\Event\HangmanPlayerWinEvent;
use Hangman\Exception\HangmanException;
use Hangman\Exception\HangmanPlayerOptionsException;
use Hangman\Move\Answer;
use Hangman\Move\Proposition;
use Hangman\Options\HangmanPlayerOptions;
use Hangman\Result\HangmanBadProposition;
use Hangman\Result\HangmanGoodProposition;
use Hangman\Result\HangmanLost;
use Hangman\Result\HangmanWon;
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
     * @var string
     */
    private $word;

    /**
     * @var HangmanPlayer[]
     **/
    private $players;

    /**
     * @var array
     */
    protected $gameOrder;

    /**
     * @var HangmanPlayer
     **/
    private $currentPlayer;

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
     * @param  PlayerId $playerId
     * @return HangmanPlayer
     */
    public function getPlayer(PlayerId $playerId = null)
    {
        if ($playerId === null) {
            return null;
        }

        return isset($this->players[(string)$playerId]) ? $this->players[(string)$playerId] : null;
    }

    /**
     * Returns the player who can play
     *
     * @return Player
     */
    public function getCurrentPlayer()
    {
        return $this->currentPlayer;
    }

    /**
     * Get the players
     *
     * @return Player[]
     */
    public function getPlayers()
    {
        return $this->players;
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
     * @param  PlayerId $playerId
     * @return bool
     */
    public function canPlayerPlay(PlayerId $playerId)
    {
        return $this->currentPlayer && $this->currentPlayer->getId()->equals($playerId);
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////   DOMAIN METHODS   /////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Starts the game
     *
     * @param  PlayerId $playerId
     *
     * @return GameResult
     */
    public function startGame(PlayerId $playerId)
    {
        if ($this->state !== self::STATE_READY) {
            $event = new HangmanGameFailedStartingEvent(
                $this->id,
                $playerId,
                HangmanGameFailedStartingEvent::BAD_STATE
            );
            $this->apply($event);
            return $event;
        }

        if (count($this->players) === 0) {
            $event = new HangmanGameFailedStartingEvent(
                $this->id,
                $playerId,
                HangmanGameFailedStartingEvent::NO_PLAYER
            );
            $this->apply($event);
            return $event;
        }

        $event = new HangmanGameStartedEvent($this->id, $playerId);
        $this->apply($event);

        $this->setNextPlayer($playerId);

        return $event;
    }

    /**
     * Adds a player to the game
     *
     * @param  PlayerOptions $playerOptions
     * @throws HangmanPlayerOptionsException
     * @throws HangmanException
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

        if ($this->state !== self::STATE_READY) {
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
     * @param  PlayerId $playerId
     * @return GameResult
     */
    public function leaveGame(PlayerId $playerId)
    {
        switch ($this->state) {
            case self::STATE_STARTED:
                return $this->playerLoses($this->getPlayer($playerId));
                break;
            case self::STATE_OVER:
                break;
            default:
                if (isset($this->players[(string) $playerId])) {
                    unset($this->players[(string) $playerId]);
                }
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
     * Player proposes a letter
     *
     * @param  PlayerId $playerId
     * @param  Proposition $move
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
     * @param  PlayerId $playerId
     * @param  Answer $move
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
     * @param  PlayerId $playerId
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
     * @param  string   $letter
     * @return HangmanBadProposition|HangmanGoodProposition
     */
    private function currentPlayerProposeLetter($letter)
    {
        $capLetter = strtoupper($letter);
        $letterPresent = $this->wordContains($capLetter);

        $result =  (!$letterPresent)
                   ? $this->currentPlayerBadProposition($letter) // remove a life
                   : $this->currentPlayerGoodProposition($letter); // yay!

        return $result;
    }

    /**
     * Propose an answer
     *
     * @param  string   $answer
     * @return HangmanLost|HangmanWon
     */
    private function currentPlayerProposeAnswer($answer)
    {
        $this->checkAnswerIsValid($answer);

        if ($this->isTheAnswer(strtoupper($answer))) {
            return $this->playerWins($this->currentPlayer); // you win
        } else {
            return $this->playerLoses($this->currentPlayer); // you lose
        }
    }

    /**
     * Function to call when a bad proposition has been made
     *
     * @param  string $letter
     *
     * @return HangmanBadProposition
     */
    private function currentPlayerBadProposition($letter)
    {
        $capLetter = strtoupper($letter);
        $player = $this->currentPlayer;
        $playerId = $player->getId();

        $playedLetters = $this->getPlayedLettersForPlayer($playerId);
        $playedLetters[$capLetter] = $capLetter;
        $wordSoFar = $this->buildWord($playedLetters);
        $livesLost = 1;
        $remainingLives = $this->getRemainingLives($playerId) - $livesLost;
        $nextPlayerId = PlayerId::create($this->getNextPlayerId());

        $event = new HangmanBadLetterProposedEvent(
            $this->id,
            $playerId,
            $capLetter,
            $playedLetters,
            $livesLost,
            $remainingLives,
            $wordSoFar
        );
        $this->apply($event);

        if ($remainingLives === 0) {
            return $this->playerLoses($player);
        }

        $this->setNextPlayer($nextPlayerId);

        return $event;
    }

    /**
     * Function to call after a good proposition of letter has been made
     *
     * @param  string $letter
     *
     * @return HangmanGoodProposition
     */
    private function currentPlayerGoodProposition($letter)
    {
        $capLetter = strtoupper($letter);
        $player = $this->currentPlayer;
        $playerId = $player->getId();

        $playedLetters = $this->getPlayedLettersForPlayer($playerId);
        $playedLetters[$capLetter] = $capLetter;
        $wordSoFar = $this->buildWord($playedLetters);
        $remainingLives = $this->getRemainingLives($playerId);
        $nextPlayerId = PlayerId::create($this->getNextPlayerId());

        $event = new HangmanGoodLetterProposedEvent(
            $this->id,
            $playerId,
            $capLetter,
            $playedLetters,
            $remainingLives,
            $wordSoFar
        );
        $this->apply($event);

        if ($this->isAllLettersFoundForPlayer($player)) {
            return $this->playerWins($player);
        }

        $this->setNextPlayer($nextPlayerId);

        return $event;
    }

    /**
     * Function to call when game is won by a player
     *
     * @param  HangmanPlayer $player
     * @return HangmanWon
     */
    private function playerWins(HangmanPlayer $player)
    {
        $playerId = $player->getId();

        $playedLetters = $this->getPlayedLettersForPlayer($playerId);
        $remainingLives = $this->getRemainingLives($playerId);

        $event = new HangmanPlayerWinEvent(
            $this->id,
            $playerId,
            $playedLetters,
            $remainingLives,
            $this->word
        );
        $this->apply($event);

        foreach ($this->players as $otherPlayer) {
            if ($otherPlayer->equals($player) || $otherPlayer->hasLost()) {
                continue;
            }
            $this->makePlayerLose($otherPlayer);
        }

        return $event;
    }

    /**
     * Just make one player lose
     *
     * @param  HangmanPlayer $player
     * @return HangmanPlayerLostEvent
     */
    private function makePlayerLose(HangmanPlayer $player)
    {
        $playerId = $player->getId();

        $playedLetters = $this->getPlayedLettersForPlayer($playerId);
        $remainingLives = $this->getRemainingLives($playerId);

        $event = new HangmanPlayerLostEvent(
            $this->id,
            $playerId,
            $playedLetters,
            $remainingLives,
            $this->buildWord($playedLetters),
            $this->word
        );
        $this->apply($event);

        return $event;
    }

    /**
     * Function to call when game is lost by a player
     *
     * @param  HangmanPlayer $player
     * @return HangmanLost
     */
    private function playerLoses(HangmanPlayer $player)
    {
        $nextPlayerId = PlayerId::create($this->getNextPlayerId());

        $event = $this->makePlayerLose($player);

        if (count($this->gameOrder) > 0 &&
            $this->currentPlayer &&
            $player->equals($this->currentPlayer)
        ) {
            $this->setNextPlayer($nextPlayerId);
            return $event;
        }

        $event = new HangmanGameLostEvent(
            $this->id,
            $player->getId(),
            $this->word
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
        if ($id === null || ($this->currentPlayer && $this->currentPlayer->getId()->equals($id))) {
            return;
        }

        $this->apply(
            new HangmanPlayerTurnEvent($this->getId(), $id)
        );
    }

    /**
     * Returns the next player in line
     *
     * @return PlayerId
     */
    private function getNextPlayerId()
    {
        if ($this->currentPlayer === null) {
            return null;
        }

        $nbPlayers = count($this->gameOrder);
        $currentPlayerId = (string)$this->currentPlayer->getId();
        $nextPlayerPosition = (array_search($currentPlayerId, $this->gameOrder) + 1) % $nbPlayers;

        $pos = $nextPlayerPosition;
        do {
            $id = PlayerId::create($this->gameOrder[$pos]);
            $player = $this->getPlayer($id);

            if ($player->getState() === HangmanPlayer::STATE_IN_GAME) {
                return $id;
            }

            $pos = ($pos + 1) % $nbPlayers;
        } while ($pos !== $nextPlayerPosition);

        return null;
    }

    /**
     * Returns the list of played letters
     *
     * @param  PlayerId $playerId
     * @return array
     */
    private function getPlayedLettersForPlayer(PlayerId $playerId)
    {
        return $this->getPlayer($playerId)->getPlayedLetters();
    }

    /**
     * Gets the remaining lives for the player
     *
     * @param  PlayerId $playerId
     * @return int
     */
    private function getRemainingLives(PlayerId $playerId)
    {
        return $this->getPlayer($playerId)->getRemainingLives();
    }

    /**
     * Returns the indexes of the letter in the word
     *
     * @param  string $letter
     * @return boolean
     */
    private function wordContains($letter)
    {
        return strpos(strtoupper($this->word), strtoupper($letter)) !== false;
    }

    /**
     * Get the letters of the word
     *
     * @return string[]
     */
    private function getLettersFromWord()
    {
        return array_unique(str_split(strtoupper($this->word)));
    }

    /**
     * Build the word from played letters
     *
     * @param  string[] $playedLetters
     * @return string
     */
    private function buildWord($playedLetters)
    {
        $wordLetters = $this->getLettersFromWord();

        $goodLetters = array_intersect($wordLetters, $playedLetters);

        $splitWord = str_split(strtoupper($this->word));
        $word = '';
        foreach ($splitWord as $letter) {
            $word .= (in_array($letter, $goodLetters) ? $letter : '_') . ' ';
        }

        return trim($word);
    }

    /**
     * Checks if all letters for the word have been found
     *
     * @param  HangmanPlayer $player
     * @return bool
     */
    private function isAllLettersFoundForPlayer(HangmanPlayer $player)
    {
        $wordLetters = $this->getLettersFromWord();
        $playerLetters = $player->getPlayedLetters();
        return count(array_intersect($wordLetters, $playerLetters)) == count($wordLetters);
    }

    /**
     * Checks if the answer is valid
     * If it's not, ends player turn and throws an HangmanException
     *
     * @param  string $answer
     * @throws HangmanException
     */
    private function checkAnswerIsValid($answer)
    {
        if (strlen($answer) !== strlen($this->word)) {
            throw new HangmanException(sprintf('"%s" is not a valid answer!', $answer));
        }
    }

    /**
     * Checks if the word is the same as the solution
     *
     * @param  string $word
     * @return bool
     */
    private function isTheAnswer($word)
    {
        return ($this->word === strtoupper($word));
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////   APPLY EVENTS   //////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Apply the game created event
     *
     * @param  HangmanGameCreatedEvent $event
     * @return void
     */
    protected function applyHangmanGameCreatedEvent(HangmanGameCreatedEvent $event)
    {
        $this->id = $event->getGameId();
        $this->word = strtoupper($event->getWord());
        $this->players = [];
        $this->gameOrder = [];
        $this->state = self::STATE_READY;
    }

    /**
     * Apply the player created event
     *
     * @param  HangmanPlayerCreatedEvent $event
     * @return void
     */
    protected function applyHangmanPlayerCreatedEvent(HangmanPlayerCreatedEvent $event)
    {
        $player = new HangmanPlayer(
            $event->getPlayerId(),
            $event->getPlayerName(),
            $event->getLives(),
            $this,
            $event->getExternalReference()
        );

        $this->gameOrder[] = (string)$player->getId();
        $this->players[(string)$player->getId()] = $player;
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
        $this->currentPlayer = $this->getPlayer($event->getPlayerId());
    }

    /**
     * Apply the hangman player lost event
     *
     * @param HangmanPlayerLostEvent $event
     */
    protected function applyHangmanPlayerLostEvent(HangmanPlayerLostEvent $event)
    {
        $this->state = self::STATE_OVER;
        unset($this->gameOrder[array_search((string) $event->getPlayerId(), $this->gameOrder)]);
    }

    /**
     * Apply the hangman player win event
     *
     * @return void
     */
    protected function applyHangmanPlayerWinEvent()
    {
        $this->currentPlayer = null;
        $this->state = self::STATE_OVER;
    }

    /**
     * Apply the hangman lost by all event
     *
     * @return void
     */
    protected function applyHangmanGameLostEvent()
    {
        $this->currentPlayer = null;
        $this->state = self::STATE_OVER;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////   EVENT SOURCED   /////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return HangmanPlayer[]
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
     * @param  MiniGameId $id
     * @param  string     $word
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
}
