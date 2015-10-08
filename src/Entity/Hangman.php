<?php
namespace Hangman\Entity;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Hangman\Event\HangmanBadLetterProposedEvent;
use Hangman\Event\HangmanGameCreatedEvent;
use Hangman\Event\HangmanGameStartedEvent;
use Hangman\Event\HangmanPlayerCreatedEvent;
use Hangman\Event\HangmanPlayerLostEvent;
use Hangman\Exception\HangmanException;
use Hangman\Move\Answer;
use Hangman\Move\Proposition;
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
use MiniGame\Exceptions\InactiveGameException;
use MiniGame\Exceptions\NotPlayerTurnException;
use MiniGame\GameResult;
use MiniGame\Move;
use MiniGame\PlayerOptions;
use Rhumsaa\Uuid\Uuid;

class Hangman extends EventSourcedAggregateRoot implements MiniGame
{
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
     * @var boolean
     */
    protected $started;

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->started = false;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////   PUBLIC METHODS   /////////////////////////////////////////////////
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
     * Starts the game
     *
     * @return void
     *
     * @throws HangmanException
     */
    public function startGame()
    {
        if ($this->started) {
            throw new HangmanException("You can't start a game that's already started.");
        }

        $this->apply(new HangmanGameStartedEvent($this->id));
    }

    /**
     * Adds a player to the game
     *
     * @param  PlayerOptions $playerOptions
     * @return void
     */
    public function addPlayerToGame(PlayerOptions $playerOptions)
    {
        $player = new HangmanPlayer(
            new PlayerId(),
            'John Doe',
            6,
            $this
        ); // TODO add hangman options

        $this->addPlayer($player);
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
     * Allows the player to play the game
     *
     * @param  PlayerId $playerId
     * @param  Move     $move
     * @return GameResult
     * @throws \Exception
     */
    public function play(PlayerId $playerId, Move $move)
    {
        if (!$this->started) {
            throw new InactiveGameException(
                $playerId,
                $this->getId(),
                $this->playerError($playerId, 'Error!'),
                'You cannot play'
            );
        }

        if (!$this->canPlayerPlay($playerId)) {
            throw new NotPlayerTurnException(
                $playerId,
                $this->getId(),
                $this->playerError($playerId, 'Error!'),
                'It is not your turn to play'
            );
        }

        try {
            if ($move instanceof Proposition) {
                return $this->currentPlayerProposeLetter($move->getText());
            } elseif ($move instanceof Answer) {
                return $this->currentPlayerProposeAnswer($move->getText());
            } else {
                throw new HangmanException('Unsupported Move!');
            }
        } catch (HangmanException $e) {
            $return = $this->currentPlayerBadProposition($move->getText());
            throw new IllegalMoveException(
                $playerId,
                $this->getId(),
                $return,
                $move,
                $e->getMessage()
            );
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////   PRIVATE METHODS   /////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Initialize the game
     *
     * @param MiniGameId      $id
     * @param string          $word
     * @param HangmanPlayer[] $players
     */
    private function initialize(MiniGameId $id, $word, array $players)
    {
        $this->apply(new HangmanGameCreatedEvent($id, $word));

        foreach ($players as $player) {
            $this->addPlayer($player);
        }
    }

    /**
     * Adds a player
     *
     * @param Player $player
     *
     * @throws HangmanException
     */
    private function addPlayer(Player $player)
    {
        if ($this->started) {
            throw new HangmanException('You cannot add a player to a game that has already started.');
        }

        $this->apply(new HangmanPlayerCreatedEvent($this->id, $player));
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
            return $this->currentPlayerWins(); // you win
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

        $this->apply(
            new HangmanBadLetterProposedEvent(
                $this->id,
                $playerId,
                $capLetter,
                $playedLetters,
                $livesLost,
                $remainingLives,
                $wordSoFar
            )
        );

        if ($remainingLives === 0) {
            return $this->playerLoses($player);
        }

        return new HangmanBadProposition(
            $this->id,
            $playerId,
            $wordSoFar,
            $playedLetters,
            $remainingLives
        ) ;
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
        $this->savePlayedLetterForCurrentPlayer($letter);

        $player = $this->currentPlayer;
        $playerId = $player->getId();

        if ($this->isAllLettersFoundForCurrentPlayer()) {
            return $this->currentPlayerWins();
        }

        $this->endCurrentPlayerTurn();

        return new HangmanGoodProposition(
            $this->id,
            $playerId,
            $this->buildPlayerWord($player),
            $this->getPlayedLettersForPlayer($playerId),
            $this->getRemainingLives($playerId)
        ) ;
    }

    /**
     * Function to call when game is won by a player
     *
     * @return HangmanWon
     */
    private function currentPlayerWins()
    {
        $playerId = $this->currentPlayer->getId();

        $result = new HangmanWon(
            $this->id,
            $playerId,
            $this->getPlayedLettersForPlayer($playerId),
            $this->getRemainingLives($playerId),
            $this->word
        );

        $this->currentPlayer = null;

        return $result;
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

        $this->apply(
            new HangmanPlayerLostEvent(
                $this->id,
                $playerId,
                $playedLetters,
                $remainingLives,
                $this->buildWord($playedLetters),
                $this->word
            )
        );

        return new HangmanLost(
            $this->id,
            $playerId,
            $playedLetters,
            $remainingLives,
            $this->word
        );
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
     * Returns the word found so far built for the player
     *
     * @param  HangmanPlayer $player
     * @return string
     */
    private function buildPlayerWord(HangmanPlayer $player)
    {
        return $this->buildWord($player->getPlayedLetters());
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
     * @return bool
     */
    private function isAllLettersFoundForCurrentPlayer()
    {
        $wordLetters = $this->getLettersFromWord();
        $playerLetters = $this->currentPlayer->getPlayedLetters();
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

    /**
     * Apply the player created event
     *
     * @param  HangmanPlayerCreatedEvent $event
     * @return void
     */
    protected function applyHangmanPlayerCreatedEvent(HangmanPlayerCreatedEvent $event)
    {
        $player = $event->getPlayer();

        $player->setGame($this);
        $this->gameOrder[] = (string)$player->getId();

        if ($this->currentPlayer === null) {
            $this->currentPlayer = $player;
        }

        $this->players[] = $player;
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
    }

    /**
     * Apply the game created event
     *
     * @param  HangmanGameStartedEvent $event
     * @return void
     */
    protected function applyHangmanGameStartedEvent(HangmanGameStartedEvent $event)
    {
        $this->started = true;
    }

    /**
     * Apply the bad letter played event
     *
     * @param  HangmanBadLetterProposedEvent $event
     * @return void
     */
    protected function applyHangmanBadLetterProposedEvent(HangmanBadLetterProposedEvent $event)
    {
        $player = $this->getPlayer($event->getPlayerId());

        $this->savePlayedLetterForCurrentPlayer($event->getLetter());
        $player->loseLife();

        $this->endCurrentPlayerTurn();
    }

    /**
     * Apply the hangman player lost event
     *
     * @param  HangmanPlayerLostEvent $event
     * @return void
     */
    protected function applyHangmanPlayerLostEvent(HangmanPlayerLostEvent $event)
    {
        $this->currentPlayer = null;
        $this->started = false;
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
     * @param  string $word
     * @param  array $players
     * @return Hangman
     */
    public static function createGame(MiniGameId $id = null, $word = 'HANGMAN', array $players = array())
    {
        $hangman = new Hangman(false);
        $hangman->initialize(
            $id ? : new MiniGameId(Uuid::uuid4()->toString()),
            $word,
            $players
        );

        return $hangman;
    }
}
