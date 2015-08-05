<?php
namespace Hangman;

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

class Hangman implements MiniGame
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
     * @var array
     */
    protected $lettersPlayed;

    /**
     * @var array
     */
    protected $badLettersPlayed;

    /**
     * @var array
     */
    protected $remainingChances;

    /**
     * @var Player
     **/
    protected $currentPlayer;

    /**
     * Constructor
     *
     * @param MiniGameId $id
     * @param string     $word
     * @param Player[]   $players
     * @param int        $chances
     */
    public function __construct(MiniGameId $id = null, $word = 'HANGMAN', array $players = array(), $chances = 6)
    {
        if ($id === null) {
            $id = new MiniGameId(Uuid::uuid4()->toString());
        }

        $this->id = $id;
        $this->word = strtoupper($word);
        $this->players = $players;

        $this->gameOrder = array();

        $this->lettersPlayed = array();
        $this->badLettersPlayed = array();

        $order = 0;
        foreach ($players as $player) {
            $playerId = $player->getId();
            $this->lettersPlayed[(string)$playerId] = array();
            $this->badLettersPlayed[(string)$playerId] = array();
            for ($i = 0; $i < strlen($this->word); $i++) {
                $this->lettersPlayed[(string)$playerId][$i] = '_';
            }
            $this->remainingChances[(string)$playerId] = $chances;
            $this->gameOrder[$order++] = (string)$playerId;
        }

        $this->currentPlayer = reset($players);
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
     * Returns the id of the game (unique string)
     *
     * @return MiniGameId
     */
    public function getId()
    {
        return $this->id;
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
                $this->error($playerId, 'Error!'),
                'It is not your turn to play'
            );
        }

        try {
            if ($move instanceof Proposition) {
                return $this->proposeLetter($playerId, $move->getText());
            } elseif ($move instanceof Answer) {
                return $this->proposeAnswer($playerId, $move->getText());
            } else {
                throw new HangmanException('Unsupported Move!');
            }
        } catch (HangmanException $e) {
            throw new IllegalMoveException(
                $playerId,
                $this->getId(),
                $this->badProposition($playerId),
                $move,
                $e->getMessage()
            );
        }
    }

    /**
     * Propose a letter
     *
     * @param  PlayerId $playerId
     * @param  string   $letter
     * @return HangmanBadProposition|HangmanGoodProposition
     */
    protected function proposeLetter(PlayerId $playerId, $letter)
    {
        $capLetter = strtoupper($letter);
        $positions = $this->contains($capLetter);
        $this->savePlayedLetter($playerId, $capLetter, $positions);

        $result =  (!$positions)
                   ? $this->badProposition($playerId) // remove a life
                   : $this->goodProposition($playerId); // yay!

        $this->endCurrentPlayerTurn();

        return $result;
    }

    /**
     * Propose an answer
     *
     * @param  PlayerId $playerId
     * @param  string   $answer
     * @return HangmanLost|HangmanWon
     */
    protected function proposeAnswer(PlayerId $playerId, $answer)
    {
        $this->checkAnswerIsValid($answer);

        if ($this->isTheAnswer($answer)) {
            return $this->win($playerId); // you win
        } else {
            return $this->lose($playerId); // you lose
        }
    }

    /**
     * Checks if the answer is valid
     * If it's not, ends player turn and throws an HangmanException
     *
     * @param  string $answer
     * @throws HangmanException
     */
    protected function checkAnswerIsValid($answer)
    {
        if (strlen($answer) !== strlen($this->word)) {
            $this->endCurrentPlayerTurn();
            throw new HangmanException(sprintf('"%s" is not a valid answer!', $answer));
        }
    }

    /**
     * Is it the player's turn?
     *
     * @param  PlayerId $player
     * @return bool
     */
    public function canPlayerPlay(PlayerId $player)
    {
        return $this->currentPlayer && $this->currentPlayer->getId() === $player;
    }

    /**
     * Gets the remaining chances for the player
     *
     * @param  PlayerId $player
     * @return int
     */
    public function getRemainingChances(PlayerId $player)
    {
        return $this->remainingChances[(string)$player];
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
     * Returns the next player in line
     *
     * @return void
     */
    protected function endCurrentPlayerTurn()
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
     * Function to call when an error must be returned
     *
     * @param  PlayerId $playerId
     * @param  string   $message
     * @return HangmanError
     */
    protected function error(PlayerId $playerId, $message)
    {
        return new HangmanError(
            $this->id,
            $playerId,
            $message,
            $this->getPlayedLetters($playerId),
            $this->getRemainingChances($playerId)
        ) ;
    }

    /**
     * Function to call after a good proposition of letter has been made
     *
     * @param  PlayerId $playerId
     * @return HangmanGoodProposition
     */
    protected function goodProposition(PlayerId $playerId)
    {
        if ($this->isAllLettersFound($playerId)) {
            return $this->win($playerId);
        }
        return new HangmanGoodProposition(
            $this->id,
            $playerId,
            $this->buildWord($playerId),
            $this->getPlayedLetters($playerId),
            $this->getRemainingChances($playerId)
        ) ;
    }

    /**
     * Function to call when a bad proposition has been made
     *
     * @param  PlayerId $playerId
     * @return HangmanBadProposition
     */
    protected function badProposition(PlayerId $playerId)
    {

        $this->remainingChances[(string)$playerId]--;

        if ($this->getRemainingChances($playerId) == 0) {
            return $this->lose($playerId);
        }

        return new HangmanBadProposition(
            $this->id,
            $playerId,
            $this->buildWord($playerId),
            $this->getPlayedLetters($playerId),
            $this->getRemainingChances($playerId)
        ) ;
    }

    /**
     * Function to call when game is won by a player
     *
     * @param  PlayerId $playerId
     * @return HangmanWon
     */
    protected function win(PlayerId $playerId)
    {
        $this->currentPlayer = null;
        return new HangmanWon(
            $this->id,
            $playerId,
            $this->getPlayedLetters($playerId),
            $this->getRemainingChances($playerId),
            $this->word
        );
    }

    /**
     * Function to call when game is lost by a player
     *
     * @param  PlayerId $playerId
     * @return HangmanLost
     */
    protected function lose(PlayerId $playerId)
    {
        $this->currentPlayer = null;
        return new HangmanLost(
            $this->id,
            $playerId,
            $this->getPlayedLetters($playerId),
            $this->getRemainingChances($playerId),
            $this->word
        );
    }

    /**
     * Checks if the word is the same as the solution
     *
     * @param  string $word
     * @return bool
     */
    protected function isTheAnswer($word)
    {
        return ($this->word === strtoupper($word));
    }

    /**
     * Returns the indexes of the letter in the word
     *
     * @param  string $letter
     * @return array
     */
    protected function contains($letter)
    {
        $lastPos = 0;
        $positions = array();

        while (($lastPos = strpos($this->word, $letter, $lastPos))!== false) {
            $positions[] = $lastPos;
            $lastPos = $lastPos + 1;
        }

        return $positions;
    }

    /**
     * Saves the letter played by the player and the result
     *
     * @param  PlayerId $playerId
     * @param  string   $letter
     * @param  array    $result
     * @return void
     */
    protected function savePlayedLetter(PlayerId $playerId, $letter, array $result)
    {
        $playerId = $playerId->getId();
        if ($result) {
            foreach ($result as $position) {
                $this->lettersPlayed[(string)$playerId][$position] = $letter;
            }
        } else {
            $this->badLettersPlayed[(string)$playerId][] = $letter;
        }
    }

    /**
     * Checks if all letters for the word have been found
     *
     * @param  PlayerId $player
     * @return bool
     */
    protected function isAllLettersFound(PlayerId $player)
    {
        return !in_array('_', $this->lettersPlayed[(string)$player]);
    }

    /**
     * Returns the word built for the player
     *
     * @param  PlayerId $playerId
     * @return string
     */
    protected function buildWord(PlayerId $playerId)
    {
        return implode(' ', $this->lettersPlayed[(string)$playerId]);
    }

    /**
     * Returns the list of played letters
     *
     * @param  PlayerId $playerId
     * @return array
     */
    protected function getPlayedLetters(PlayerId $playerId)
    {
        $letters = $this->lettersPlayed[(string)$playerId];
        $badLetters = $this->badLettersPlayed[(string)$playerId];
        return array_unique(
            array_merge(
                $badLetters,
                array_filter(
                    $letters,
                    function ($l) {
                        return $l != '_';
                    }
                )
            )
        );
    }
}
