<?php
namespace Hangman;

use Hangman\Exception\HangmanException;
use Hangman\Result\HangmanBadProposition;
use Hangman\Result\HangmanError;
use Hangman\Result\HangmanGoodProposition;
use Hangman\Result\HangmanLost;
use Hangman\Result\HangmanWon;
use MiniGame\Exceptions\IllegalMoveException;
use MiniGame\Exceptions\NotPlayerTurnException;
use MiniGame\GameResult;
use MiniGame\MiniGame;
use MiniGame\Move;
use MiniGame\Player;
use Hangman\Move\Answer;
use Hangman\Move\Proposition;
use Rhumsaa\Uuid\Uuid;

class Hangman implements MiniGame
{

    /**
     * @var string
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
     * @param string   $id
     * @param string   $word
     * @param Player[] $players
     * @param int      $chances
     */
    public function __construct($id = null, $word = 'HANGMAN', array $players = array(), $chances = 6)
    {
        if ($id === null) {
            $id = Uuid::uuid4()->toString();
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
            $this->lettersPlayed[$playerId] = array();
            $this->badLettersPlayed[$playerId] = array();
            for ($i = 0; $i < strlen($this->word); $i++) {
                $this->lettersPlayed[$playerId][$i] = '_';
            }
            $this->remainingChances[$playerId] = $chances;
            $this->gameOrder[$order++] = $playerId;
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
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Allows the player to play the game
     *
     * @param  Player $player
     * @param  Move   $move
     * @return GameResult
     * @throws \Exception
     */
    public function play(Player $player, Move $move)
    {
        if (!$this->canPlayerPlay($player)) {
            throw new NotPlayerTurnException(
                $player,
                $this,
                $this->error($player, 'Error!'),
                'It is not your turn to play'
            );
        }

        try {
            if ($move instanceof Proposition) {
                return $this->proposeLetter($player, $move->getText());
            } elseif ($move instanceof Answer) {
                return $this->proposeAnswer($player, $move->getText());
            } else {
                throw new HangmanException('Unsupported Move!');
            }
        } catch (HangmanException $e) {
            throw new IllegalMoveException($player, $this, $this->badProposition($player), $move, $e->getMessage());
        }
    }

    /**
     * Propose a letter
     *
     * @param  Player $player
     * @param  string $letter
     * @return HangmanBadProposition|HangmanGoodProposition
     */
    protected function proposeLetter(Player $player, $letter)
    {
        $capLetter = strtoupper($letter);
        $positions = $this->contains($capLetter);
        $this->savePlayedLetter($player, $capLetter, $positions);

        $result =  (!$positions)
                   ? $this->badProposition($player) // remove a life
                   : $this->goodProposition($player); // yay!

        $this->endCurrentPlayerTurn();

        return $result;
    }

    /**
     * Propose an answer
     *
     * @param  Player $player
     * @param  string $answer
     * @return HangmanLost|HangmanWon
     */
    protected function proposeAnswer(Player $player, $answer)
    {
        $this->checkAnswerIsValid($answer);

        if ($this->isTheAnswer($answer)) {
            return $this->win($player); // you win
        } else {
            return $this->lose($player); // you lose
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
     * @param  Player $player
     * @return bool
     */
    public function canPlayerPlay(Player $player)
    {
        return $this->currentPlayer && $this->currentPlayer->getId() === $player->getId();
    }

    /**
     * Gets the remaining chances for the player
     *
     * @param  Player $player
     * @return int
     */
    public function getRemainingChances(Player $player)
    {
        return $this->remainingChances[$player->getId()];
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

        $currentPlayerId = $this->currentPlayer->getId();
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
            if ($player->getId() == $nextPlayerId) {
                $nextPlayer = $player;
                break;
            }
        }

        $this->currentPlayer = $nextPlayer;
    }

    /**
     * Function to call when an error must be returned
     *
     * @param  Player $player
     * @param  string $message
     * @return HangmanError
     */
    protected function error(Player $player, $message)
    {
        return new HangmanError(
            $message,
            $player,
            $this->getPlayedLetters($player),
            $this->getRemainingChances($player)
        ) ;
    }

    /**
     * Function to call after a good proposition of letter has been made
     *
     * @param  Player $player
     * @return HangmanGoodProposition
     */
    protected function goodProposition(Player $player)
    {
        if ($this->isAllLettersFound($player)) {
            return $this->win($player);
        }
        return new HangmanGoodProposition(
            $player,
            $this->buildWord($player),
            $this->getPlayedLetters($player),
            $this->getRemainingChances($player)
        ) ;
    }

    /**
     * Function to call when a bad proposition has been made
     *
     * @param  Player $player
     * @return HangmanBadProposition
     */
    protected function badProposition(Player $player)
    {

        $this->remainingChances[$player->getId()]--;

        if ($this->getRemainingChances($player) == 0) {
            return $this->lose($player);
        }

        return new HangmanBadProposition(
            $player,
            $this->buildWord($player),
            $this->getPlayedLetters($player),
            $this->getRemainingChances($player)
        ) ;
    }

    /**
     * Function to call when game is won by a player
     *
     * @param  Player $player
     * @return HangmanWon
     */
    protected function win(Player $player)
    {
        $this->currentPlayer = null;
        return new HangmanWon(
            $player,
            $this->getPlayedLetters($player),
            $this->getRemainingChances($player),
            $this->word
        );
    }

    /**
     * Function to call when game is lost by a player
     *
     * @param  Player $player
     * @return HangmanLost
     */
    protected function lose(Player $player)
    {
        $this->currentPlayer = null;
        return new HangmanLost(
            $player,
            $this->getPlayedLetters($player),
            $this->getRemainingChances($player),
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
     * @param  Player $player
     * @param  string $letter
     * @param  array  $result
     * @return void
     */
    protected function savePlayedLetter(Player $player, $letter, array $result)
    {
        $playerId = $player->getId();
        if ($result) {
            foreach ($result as $position) {
                $this->lettersPlayed[$playerId][$position] = $letter;
            }
        } else {
            $this->badLettersPlayed[$playerId][] = $letter;
        }
    }

    /**
     * Checks if all letters for the word have been found
     *
     * @param  Player $player
     * @return bool
     */
    protected function isAllLettersFound(Player $player)
    {
        return !in_array('_', $this->lettersPlayed[$player->getId()]);
    }

    /**
     * Returns the word built for the player
     *
     * @param  Player $player
     * @return string
     */
    protected function buildWord(Player $player)
    {
        return implode(' ', $this->lettersPlayed[$player->getId()]);
    }

    /**
     * Returns the list of played letters
     *
     * @param  Player $player
     * @return array
     */
    protected function getPlayedLetters(Player $player)
    {
        $playerId = $player->getId();
        $letters = $this->lettersPlayed[$playerId];
        $badLetters = $this->badLettersPlayed[$playerId];
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
