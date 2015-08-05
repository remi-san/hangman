<?php
namespace Hangman\Test;

use Hangman\Result\HangmanBadProposition;
use Hangman\Result\HangmanError;
use Hangman\Result\HangmanGoodProposition;
use Hangman\Result\HangmanLost;
use Hangman\Result\HangmanWon;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\Player;
use MiniGame\Entity\PlayerId;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanResultTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    /**
     * @var Player
     */
    private $player;

    /**
     * @var PlayerId
     */
    private $playerId;

    /**
     * @var MiniGameId
     */
    private $gameId;

    private $lettersPlayed;

    public function setUp()
    {
        $this->playerId = $this->getPlayerId(42);
        $this->gameId = $this->getMiniGameId(666);
        $this->player = $this->getPlayer($this->playerId, 'Douglas');
        $this->lettersPlayed = array('A', 'E', 'Z');
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testBadProposition()
    {
        $feedback = 'feedback';
        $remainingChances = 5;
        $message = sprintf(
            'Too bad... %s (letters played: %s) - Remaining chances: %d',
            $feedback,
            implode(', ', $this->lettersPlayed),
            $remainingChances
        );

        $badProposition = new HangmanBadProposition(
            $this->gameId,
            $this->playerId,
            $feedback,
            $this->lettersPlayed,
            $remainingChances
        );

        $this->assertEquals($this->gameId, $badProposition->getGameId());
        $this->assertEquals($this->playerId, $badProposition->getPlayerId());
        $this->assertEquals($feedback, $badProposition->getFeedBack());
        $this->assertEquals($this->lettersPlayed, $badProposition->getLettersPlayed());
        $this->assertEquals($remainingChances, $badProposition->getRemainingChances());
        $this->assertEquals($message, $badProposition->getAsMessage());
    }

    /**
     * @test
     */
    public function testGoodProposition()
    {
        $feedback = 'feedback';
        $remainingChances = 6;
        $message = sprintf(
            'Well played! %s (letters played: %s) - Remaining chances: %d',
            $feedback,
            implode(', ', $this->lettersPlayed),
            $remainingChances
        );

        $badProposition = new HangmanGoodProposition(
            $this->gameId,
            $this->playerId,
            $feedback,
            $this->lettersPlayed,
            $remainingChances
        );

        $this->assertEquals($this->gameId, $badProposition->getGameId());
        $this->assertEquals($this->playerId, $badProposition->getPlayerId());
        $this->assertEquals($feedback, $badProposition->getFeedBack());
        $this->assertEquals($this->lettersPlayed, $badProposition->getLettersPlayed());
        $this->assertEquals($remainingChances, $badProposition->getRemainingChances());
        $this->assertEquals($message, $badProposition->getAsMessage());
    }

    /**
     * @test
     */
    public function testLost()
    {
        $solution = 'solution';
        $remainingChances = 0;
        $message = sprintf('You lose... The word was %s.', $solution);

        $badProposition = new HangmanLost(
            $this->gameId,
            $this->playerId,
            $this->lettersPlayed,
            $remainingChances,
            $solution
        );

        $this->assertEquals($this->gameId, $badProposition->getGameId());
        $this->assertEquals($this->playerId, $badProposition->getPlayerId());
        $this->assertEquals($solution, $badProposition->getSolution());
        $this->assertEquals($this->lettersPlayed, $badProposition->getLettersPlayed());
        $this->assertEquals($remainingChances, $badProposition->getRemainingChances());
        $this->assertEquals($message, $badProposition->getAsMessage());
    }

    /**
     * @test
     */
    public function testWon()
    {
        $solution = 'solution';
        $remainingChances = 0;
        $message = sprintf('Congratulations! The word was %s.', $solution);

        $badProposition = new HangmanWon(
            $this->gameId,
            $this->playerId,
            $this->lettersPlayed,
            $remainingChances,
            $solution
        );

        $this->assertEquals($this->gameId, $badProposition->getGameId());
        $this->assertEquals($this->playerId, $badProposition->getPlayerId());
        $this->assertEquals($solution, $badProposition->getSolution());
        $this->assertEquals($this->lettersPlayed, $badProposition->getLettersPlayed());
        $this->assertEquals($remainingChances, $badProposition->getRemainingChances());
        $this->assertEquals($message, $badProposition->getAsMessage());
    }

    /**
     * @test
     */
    public function testError()
    {
        $remainingChances = 0;
        $message = 'error';

        $badProposition = new HangmanError(
            $this->gameId,
            $this->playerId,
            $message,
            $this->lettersPlayed,
            $remainingChances
        );

        $this->assertEquals($this->gameId, $badProposition->getGameId());
        $this->assertEquals($this->playerId, $badProposition->getPlayerId());
        $this->assertEquals($this->lettersPlayed, $badProposition->getLettersPlayed());
        $this->assertEquals($remainingChances, $badProposition->getRemainingChances());
        $this->assertEquals($message, $badProposition->getAsMessage());
    }
}
