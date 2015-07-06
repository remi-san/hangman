<?php
namespace Hangman\Test;

use Hangman\Result\HangmanBadProposition;
use Hangman\Result\HangmanError;
use Hangman\Result\HangmanGoodProposition;
use Hangman\Result\HangmanLost;
use Hangman\Result\HangmanWon;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanResultTest extends \PHPUnit_Framework_TestCase {
    use GameObjectMocker;

    private $player;

    private $lettersPlayed;

    public function setUp()
    {
        $this->player = $this->getPlayer(42, 'Douglas');
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
        $message = sprintf('Too bad... %s (letters played: %s) - Remaining chances: %d', $feedback, implode(', ', $this->lettersPlayed), $remainingChances);

        $badProposition = new HangmanBadProposition($this->player, $feedback, $this->lettersPlayed, $remainingChances);

        $this->assertEquals($this->player, $badProposition->getPlayer());
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
        $message = sprintf('Well played! %s (letters played: %s) - Remaining chances: %d', $feedback, implode(', ', $this->lettersPlayed), $remainingChances);

        $badProposition = new HangmanGoodProposition($this->player, $feedback, $this->lettersPlayed, $remainingChances);

        $this->assertEquals($this->player, $badProposition->getPlayer());
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

        $badProposition = new HangmanLost($this->player, $this->lettersPlayed, $remainingChances, $solution);

        $this->assertEquals($this->player, $badProposition->getPlayer());
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

        $badProposition = new HangmanWon($this->player, $this->lettersPlayed, $remainingChances, $solution);

        $this->assertEquals($this->player, $badProposition->getPlayer());
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

        $badProposition = new HangmanError($message, $this->player, $this->lettersPlayed, $remainingChances);

        $this->assertEquals($this->player, $badProposition->getPlayer());
        $this->assertEquals($this->lettersPlayed, $badProposition->getLettersPlayed());
        $this->assertEquals($remainingChances, $badProposition->getRemainingChances());
        $this->assertEquals($message, $badProposition->getAsMessage());
    }
} 