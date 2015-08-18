<?php
namespace Hangman\Entity;

use Broadway\EventSourcing\EventSourcedAggregateRoot;
use Hangman\Event\HangmanGameCreatedEvent;
use Hangman\Event\HangmanPlayerCreatedEvent;
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
use MiniGame\Exceptions\NotPlayerTurnException;
use MiniGame\GameResult;
use MiniGame\Move;
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
     * Constructor
     *
     * @param MiniGameId $id
     */
    private function __construct(MiniGameId $id = null)
    {
        if ($id === null) {
            $id = new MiniGameId(Uuid::uuid4()->toString());
        }

        $this->id = $id;
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
     * Adds a player
     *
     * @param Player $player
     */
    public function addPlayer(Player $player)
    {
        $this->apply(new HangmanPlayerCreatedEvent($this->id, $player));
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
            $return = $this->currentPlayerBadProposition();
            $this->endCurrentPlayerTurn();
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
     * @param string          $word
     * @param HangmanPlayer[] $players
     */
    private function initialize($word, array $players)
    {
        $this->apply(new HangmanGameCreatedEvent($this->id, $word));

        foreach ($players as $player) {
            $this->addPlayer($player);
        }
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
        $this->savePlayedLetterForCurrentPlayer($capLetter);

        $result =  (!$letterPresent)
                   ? $this->currentPlayerBadProposition() // remove a life
                   : $this->currentPlayerGoodProposition(); // yay!

        $this->endCurrentPlayerTurn();

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
            return $this->currentPlayerLoses(); // you lose
        }
    }

    /**
     * Function to call when a bad proposition has been made
     *
     * @return HangmanBadProposition
     */
    private function currentPlayerBadProposition()
    {
        $playerId = $this->currentPlayer->getId();
        $this->currentPlayer->loseLife();

        if ($this->getRemainingLives($playerId) == 0) {
            return $this->currentPlayerLoses();
        }

        return new HangmanBadProposition(
            $this->id,
            $playerId,
            $this->buildCurrentPlayerWord(),
            $this->getPlayedLettersForCurrentPlayer(),
            $this->getRemainingLives($playerId)
        ) ;
    }

    /**
     * Function to call after a good proposition of letter has been made
     *
     * @return HangmanGoodProposition
     */
    private function currentPlayerGoodProposition()
    {
        $playerId = $this->currentPlayer->getId();

        if ($this->isAllLettersFoundForCurrentPlayer()) {
            return $this->currentPlayerWins();
        }
        return new HangmanGoodProposition(
            $this->id,
            $playerId,
            $this->buildCurrentPlayerWord(),
            $this->getPlayedLettersForCurrentPlayer(),
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
            $this->getPlayedLettersForCurrentPlayer(),
            $this->getRemainingLives($playerId),
            $this->word
        );

        $this->currentPlayer = null;

        return $result;
    }

    /**
     * Function to call when game is lost by a player
     *
     * @return HangmanLost
     */
    private function currentPlayerLoses()
    {
        $playerId = $this->currentPlayer->getId();

        $result = new HangmanLost(
            $this->id,
            $this->currentPlayer->getId(),
            $this->getPlayedLettersForCurrentPlayer(),
            $this->getRemainingLives($playerId),
            $this->word
        );

        $this->currentPlayer = null;

        return $result;
    }

    /**
     * Returns the next player in line
     *
     * @return void
     */
    private function endCurrentPlayerTurn()
    {
        if ($this->currentPlayer === null) {
            return;
        }

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
     * @return array
     */
    private function getPlayedLettersForCurrentPlayer()
    {
        return $this->currentPlayer->getPlayedLetters();
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
     * @return string
     */
    private function buildCurrentPlayerWord()
    {
        $wordLetters = $this->getLettersFromWord();
        $playerLetters = $this->currentPlayer->getPlayedLetters();
        $goodLetters = array_intersect($wordLetters, $playerLetters);

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
        $this->word = strtoupper($event->getWord());
        $this->players = array();

        $this->gameOrder = array();
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
        $hangman = new Hangman($id);
        $hangman->initialize($word, $players);

        return $hangman;
    }
}
