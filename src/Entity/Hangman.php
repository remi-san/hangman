<?php
namespace Hangman\Entity;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Hangman\Event\HangmanBadLetterProposedEvent;
use Hangman\Event\HangmanGameCreatedEvent;
use Hangman\Event\HangmanGameFailedStartingEvent;
use Hangman\Event\HangmanGameStartedEvent;
use Hangman\Event\HangmanGoodLetterProposedEvent;
use Hangman\Event\HangmanPlayerCreatedEvent;
use Hangman\Event\HangmanPlayerFailedCreatingEvent;
use Hangman\Event\HangmanPlayerLostEvent;
use Hangman\Event\HangmanPlayerProposedInvalidAnswerEvent;
use Hangman\Event\HangmanPlayerTriedPlayingDuringAnotherPlayerTurnEvent;
use Hangman\Event\HangmanPlayerTriedPlayingInactiveGameEvent;
use Hangman\Event\HangmanPlayerWinEvent;
use Hangman\Exception\HangmanException;
use Hangman\Exception\HangmanPlayerOptionsException;
use Hangman\Move\Answer;
use Hangman\Move\Proposition;
use Hangman\Options\HangmanPlayerOptions;
use Hangman\Result\HangmanBadProposition;
use Hangman\Result\HangmanError;
use Hangman\Result\HangmanGoodProposition;
use Hangman\Result\HangmanLost;
use Hangman\Result\HangmanWon;
use MiniGame\Entity\MiniGame;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\Player;
use MiniGame\Entity\PlayerId;
use MiniGame\Exceptions\IllegalMoveException;
use MiniGame\GameResult;
use MiniGame\Move;
use MiniGame\PlayerOptions;

class Hangman extends EventSourcedAggregateRoot implements MiniGame
{
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
    protected $id;

    /**
     * @var string
     */
    protected $word;

    /**
     * @var Player[]
     **/
    protected $players;

    /**
     * @var array
     */
    protected $gameOrder;

    /**
     * @var HangmanPlayer
     **/
    protected $currentPlayer;

    /**
     * @var string
     */
    protected $state;

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
    public function getPlayer(PlayerId $playerId)
    {
        foreach ($this->players as $player) {
            if ((string)$player->getId() == (string)$playerId) {
                return $player;
            }
        }
        return null;
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
     * Is it the player's turn?
     *
     * @param  PlayerId $playerId
     * @return bool
     */
    public function canPlayerPlay(PlayerId $playerId)
    {
        return $this->currentPlayer && $this->currentPlayer->getId() === $playerId;
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
     * @return void
     */
    public function startGame(PlayerId $playerId = null)
    {
        if ($this->state !== self::STATE_READY) {
            $this->apply(
                new HangmanGameFailedStartingEvent($this->id, $playerId, HangmanGameFailedStartingEvent::BAD_STATE)
            );
        }

        if (count($this->players) === 0) {
            $this->apply(
                new HangmanGameFailedStartingEvent($this->id, $playerId, HangmanGameFailedStartingEvent::BAD_STATE)
            );
        }

        $this->apply(new HangmanGameStartedEvent($this->id, $playerId));
    }

    /**
     * Adds a player to the game
     *
     * @param  PlayerOptions $playerOptions
     * @return void
     * @throws HangmanPlayerOptionsException
     * @throws HangmanException
     */
    public function addPlayerToGame(PlayerOptions $playerOptions)
    {
        if (! $playerOptions instanceof HangmanPlayerOptions) {
            throw new HangmanPlayerOptionsException(
                $playerOptions->getPlayerId(),
                $this->getId(),
                $this->playerError(
                    $playerOptions->getPlayerId(),
                    'Player options must be compatible with a hangman game.'
                ),
                'Error'
            );
        }

        if ($this->state !== self::STATE_READY) {
            $this->apply(
                new HangmanPlayerFailedCreatingEvent(
                    $this->id,
                    $playerOptions->getPlayerId(),
                    $playerOptions->getExternalReference()
                )
            );
        }

        $this->apply(
            new HangmanPlayerCreatedEvent(
                $this->id,
                $playerOptions->getPlayerId(),
                $playerOptions->getName(),
                $playerOptions->getLives(),
                $playerOptions->getExternalReference()
            )
        );
    }

    /**
     * Allows the player to play the game
     *
     * @param  PlayerId $playerId
     * @param  Move     $move
     * @return GameResult
     * @throws \Exception
     */
    public function play(PlayerId $playerId, Move $move)
    {
        if ($this->state !== self::STATE_STARTED) {
            $this->apply(
                new HangmanPlayerTriedPlayingInactiveGameEvent(
                    $this->getId(),
                    $playerId
                )
            );
        }

        if (!$this->canPlayerPlay($playerId)) {
            $this->apply(
                new HangmanPlayerTriedPlayingDuringAnotherPlayerTurnEvent(
                    $this->getId(),
                    $playerId
                )
            );
        }

        if ($move instanceof Proposition) {
            return $this->currentPlayerProposeLetter($move->getText());
        } elseif ($move instanceof Answer) {
            try {
                return $this->currentPlayerProposeAnswer($move->getText());
            } catch (HangmanException $e) {
                $this->apply(
                    new HangmanPlayerProposedInvalidAnswerEvent(
                        $this->getId(),
                        $playerId,
                        $move
                    )
                );
            }
        } else {
            throw new IllegalMoveException($move, 'Error');
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
     * Returns the next player in line
     *
     * @return void
     */
    private function endCurrentPlayerTurn()
    {
        $currentPlayerId = (string)$this->currentPlayer->getId();
        $nextPlayerId = null;

        $stop = false;
        foreach ($this->gameOrder as $pId) {
            if ($stop) {
                $nextPlayerId = $pId;
                break;
            } elseif ($currentPlayerId == $pId) {
                $stop = true;
            }

            if ($nextPlayerId === null) {
                $nextPlayerId = $pId;
            }
        }

        $nextPlayer = null;
        foreach ($this->players as $player) {
            if ((string)$player->getId() == $nextPlayerId) {
                $nextPlayer = $player;
                break;
            }
        }

        $this->currentPlayer = $nextPlayer;
    }

    /**
     * Saves the letter played by the player and the result
     *
     * @param  string   $letter
     * @return void
     */
    private function savePlayedLetterForCurrentPlayer($letter)
    {
        $this->currentPlayer->playLetter($letter);
    }

    /**
     * Returns the list of played letters
     *
     * @param  PlayerId $playerId
     * @return array
     */
    private function getPlayedLettersForPlayer(PlayerId $playerId)
    {
        $player = $this->getPlayer($playerId);
        return $player->getPlayedLetters();
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

    /**
     * Function to call when an error must be returned
     *
     * @param  PlayerId $playerId
     * @param  string   $message
     * @return HangmanError
     */
    private function playerError(PlayerId $playerId, $message)
    {
        return new HangmanError($this->id, $playerId, $message) ;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////   APPLY EVENTS   //////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    protected function applyHangmanGameFailedStartingEvent(HangmanGameFailedStartingEvent $event)
    {
        throw $event->getException(); // TODO delete once all is event based
    }

    protected function applyHangmanPlayerFailedCreatingEvent(HangmanPlayerFailedCreatingEvent $event)
    {
        throw $event->getException(); // TODO delete once all is event based
    }

    protected function applyHangmanPlayerTriedPlayingInactiveGameEvent(
        HangmanPlayerTriedPlayingInactiveGameEvent $event
    ) {
        throw $event->getException(); // TODO delete once all is event based
    }

    protected function applyHangmanPlayerTriedPlayingDuringAnotherPlayerTurnEvent(
        HangmanPlayerTriedPlayingDuringAnotherPlayerTurnEvent $event
    ) {
        throw $event->getException(); // TODO delete once all is event based
    }

    protected function applyHangmanPlayerProposedInvalidAnswerEvent(
        HangmanPlayerProposedInvalidAnswerEvent $event
    ) {
        throw $event->getException(); // TODO delete once all is event based
    }

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
        $this->players = array();

        $this->gameOrder = array();

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

        if ($this->currentPlayer === null) {
            $this->currentPlayer = $player;
        }

        $this->players[] = $player;
    }

    /**
     * Apply the game created event
     *
     * @return void
     */
    protected function applyHangmanGameStartedEvent()
    {
        $this->state = self::STATE_STARTED;
    }

    /**
     * Apply the bad letter played event
     *
     * @param  HangmanBadLetterProposedEvent $event
     * @return void
     */
    protected function applyHangmanBadLetterProposedEvent(HangmanBadLetterProposedEvent $event)
    {
        $this->savePlayedLetterForCurrentPlayer($event->getLetter());

        $this->endCurrentPlayerTurn();
    }

    /**
     * Apply the good letter played event
     *
     * @param  HangmanGoodLetterProposedEvent $event
     * @return void
     */
    protected function applyHangmanGoodLetterProposedEvent(HangmanGoodLetterProposedEvent $event)
    {
        $this->savePlayedLetterForCurrentPlayer($event->getLetter());

        $this->endCurrentPlayerTurn();
    }

    /**
     * Apply the hangman player lost event
     *
     * @return void
     */
    protected function applyHangmanPlayerLostEvent()
    {
        $this->currentPlayer = null;
        $this->state = self::STATE_OVER;
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

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////   EVENT SOURCED   /////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return array
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
     * @param  MiniGameId             $id
     * @param  string                 $word
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
