<?php
namespace Hangman\Test;

use Hangman\Hangman;
use Hangman\Result\HangmanBadProposition;
use Hangman\Result\HangmanGoodProposition;
use Hangman\Result\HangmanLost;
use Hangman\Result\HangmanWon;
use Hangman\Test\Mock\HangmanMocker;
use MiniGame\Exceptions\IllegalMoveException;
use MiniGame\Exceptions\NotPlayerTurnException;
use MiniGame\Test\Mock\GameObjectMocker;
use Rhumsaa\Uuid\Uuid;

class HangmanTest extends \PHPUnit_Framework_TestCase {
    use GameObjectMocker;
    use HangmanMocker;

    const WORD = 'HITCHHICKER';
    const ID = 42;
    const CHANCES = 5;

    const P1_ID = 314;
    const P1_NAME = 'John';

    const P2_ID = 666;
    const P2_NAME = 'James';

    /**
     * @var \Hangman\Hangman
     */
    protected $hangman;

    /**
     * @var \MiniGame\Player
     */
    protected $playerOne;

    /**
     * @var \MiniGame\Player
     */
    protected $playerTwo;

    public function setUp() {
        $this->playerOne = $this->getPlayer(self::P1_ID, self::P1_NAME);
        $this->playerTwo = $this->getPlayer(self::P2_ID, self::P2_NAME);
        $this->hangman = new Hangman(self::ID, self::WORD, array($this->playerOne, $this->playerTwo), self::CHANCES);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testGetters() {
        $this->assertEquals('HANGMAN', $this->hangman->getName());
        $this->assertEquals(self::ID, $this->hangman->getId());
        $this->assertEquals($this->playerOne, $this->hangman->getCurrentPlayer());
    }

    /**
     * @test
     */
    public function testUuidIsGenerated() {
        $hangman = new Hangman(null, self::WORD);
        $this->assertTrue(Uuid::isValid($hangman->getId()));
    }

    /**
     * @test
     */
    public function testCanPlay() {
        $this->assertTrue($this->hangman->canPlay($this->playerOne));
        $this->assertFalse($this->hangman->canPlay($this->playerTwo));
    }

    /**
     * @test
     */
    public function testPlayerOnePlaysOneGoodLetter() {
        /* @var $feedback HangmanGoodProposition */
        $feedback = $this->hangman->play($this->playerOne, $this->getProposition('H'));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanGoodProposition', $feedback);
        $this->assertEquals($this->playerOne, $feedback->getPlayer());
        $this->assertEquals(array('H'), $feedback->getLettersPlayed());
        $this->assertEquals(self::CHANCES, $feedback->getRemainingChances());
        $this->assertEquals('H _ _ _ H H _ _ _ _ _', $feedback->getFeedBack());

        $this->assertEquals(self::CHANCES, $this->hangman->getRemainingChances($this->playerOne));
        $this->assertEquals(self::CHANCES, $this->hangman->getRemainingChances($this->playerTwo));

        $this->assertFalse($this->hangman->canPlay($this->playerOne));
        $this->assertTrue($this->hangman->canPlay($this->playerTwo));
    }

    /**
     * @test
     */
    public function testPlayerOnePlaysOneBadLetter() {
        /* @var $feedback HangmanBadProposition */
        $feedback = $this->hangman->play($this->playerOne, $this->getProposition('Z'));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanBadProposition', $feedback);
        $this->assertEquals($this->playerOne, $feedback->getPlayer());
        $this->assertEquals(array('Z'), $feedback->getLettersPlayed());
        $this->assertEquals(self::CHANCES-1, $feedback->getRemainingChances());
        $this->assertEquals('_ _ _ _ _ _ _ _ _ _ _', $feedback->getFeedBack());

        $this->assertEquals(self::CHANCES-1, $this->hangman->getRemainingChances($this->playerOne));
        $this->assertEquals(self::CHANCES, $this->hangman->getRemainingChances($this->playerTwo));

        $this->assertFalse($this->hangman->canPlay($this->playerOne));
        $this->assertTrue($this->hangman->canPlay($this->playerTwo));
    }

    /**
     * @test
     */
    public function testPlayerOnePlaysIllegalAnswer() {
        $this->setExpectedException('\\MiniGame\\Exceptions\\IllegalMoveException');
        $move = $this->getAnswer('ABCD');
        try {
            $this->hangman->play($this->playerOne, $move);
        } catch (IllegalMoveException $e) {
            $this->assertEquals($move, $e->getMove());

            $this->assertEquals(self::CHANCES-1, $this->hangman->getRemainingChances($this->playerOne));
            $this->assertEquals(self::CHANCES, $this->hangman->getRemainingChances($this->playerTwo));

            $this->assertFalse($this->hangman->canPlay($this->playerOne));
            $this->assertTrue($this->hangman->canPlay($this->playerTwo));

            throw $e;
        }
    }

    /**
     * @test
     */
    public function testPlayerOneFindsSolution() {
        /* @var $feedback \Hangman\Result\HangmanWon */
        $feedback = $this->hangman->play($this->playerOne, $this->getAnswer(self::WORD));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanWon', $feedback);
        $this->assertEquals($this->playerOne, $feedback->getPlayer());
        $this->assertEquals(array(), $feedback->getLettersPlayed());
        $this->assertEquals(self::CHANCES, $feedback->getRemainingChances());
        $this->assertEquals(self::WORD, $feedback->getSolution());

        $this->assertEquals(self::CHANCES, $this->hangman->getRemainingChances($this->playerOne));
        $this->assertEquals(self::CHANCES, $this->hangman->getRemainingChances($this->playerTwo));

        $this->assertFalse($this->hangman->canPlay($this->playerOne));
        $this->assertFalse($this->hangman->canPlay($this->playerTwo));
    }

    /**
     * @test
     */
    public function testPlayerOneFindsLastLetter() {
        $word = 'AAAA';

        /* @var $feedback \Hangman\Result\HangmanWon */
        $hangman = new Hangman(self::ID, $word, array($this->playerOne, $this->playerTwo), self::CHANCES);
        $feedback = $hangman->play($this->playerOne, $this->getProposition('A'));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanWon', $feedback);
        $this->assertEquals($this->playerOne, $feedback->getPlayer());
        $this->assertEquals(array('A'), $feedback->getLettersPlayed());
        $this->assertEquals(self::CHANCES, $feedback->getRemainingChances());
        $this->assertEquals($word, $feedback->getSolution());

        $this->assertEquals(self::CHANCES, $hangman->getRemainingChances($this->playerOne));
        $this->assertEquals(self::CHANCES, $hangman->getRemainingChances($this->playerTwo));

        $this->assertFalse($hangman->canPlay($this->playerOne));
        $this->assertFalse($hangman->canPlay($this->playerTwo));
    }

    /**
     * @test
     */
    public function testPlayerOneLoses() {
        $hangman = new Hangman(self::ID, self::WORD, array($this->playerOne, $this->playerTwo), 1);

        /* @var $feedback \Hangman\Result\HangmanLost */
        $feedback = $hangman->play($this->playerOne, $this->getProposition('Z'));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanLost', $feedback);
        $this->assertEquals($this->playerOne, $feedback->getPlayer());
        $this->assertEquals(array('Z'), $feedback->getLettersPlayed());
        $this->assertEquals(0, $feedback->getRemainingChances());
        $this->assertEquals(self::WORD, $feedback->getSolution());

        $this->assertEquals(0, $hangman->getRemainingChances($this->playerOne));
        $this->assertEquals(1, $hangman->getRemainingChances($this->playerTwo));

        $this->assertFalse($hangman->canPlay($this->playerOne));
        $this->assertTrue($hangman->canPlay($this->playerTwo));
    }

    /**
     * @test
     */
    public function testPlayerOneBadSolution() {
        /* @var $feedback HangmanLost */
        $feedback = $this->hangman->play($this->playerOne, $this->getAnswer('HHHHHHHHHHH'));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanLost', $feedback);
        $this->assertEquals($this->playerOne, $feedback->getPlayer());
        $this->assertEquals(array(), $feedback->getLettersPlayed());
        $this->assertEquals(self::CHANCES, $feedback->getRemainingChances());
        $this->assertEquals(self::WORD, $feedback->getSolution());

        $this->assertEquals(self::CHANCES, $this->hangman->getRemainingChances($this->playerOne));
        $this->assertEquals(self::CHANCES, $this->hangman->getRemainingChances($this->playerTwo));

        $this->assertFalse($this->hangman->canPlay($this->playerOne));
        $this->assertTrue($this->hangman->canPlay($this->playerTwo));
    }

    /**
     * @test
     */
    public function testPlayerTwoPlaysWhenNotHisTurn() {
        $this->setExpectedException('\\MiniGame\\Exceptions\\NotPlayerTurnException');
        try {
            $this->hangman->play($this->playerTwo, $this->getProposition('A'));
        } catch (NotPlayerTurnException $e) {
            $this->assertEquals($this->playerTwo, $e->getPlayer());
            $this->assertEquals($this->hangman, $e->getMiniGame());

            $this->assertEquals(self::CHANCES, $this->hangman->getRemainingChances($this->playerOne));
            $this->assertEquals(self::CHANCES, $this->hangman->getRemainingChances($this->playerTwo));

            $this->assertTrue($this->hangman->canPlay($this->playerOne));
            throw $e;
        }
    }

    /**
     * @test
     */
    public function testTwoPlayersPlayOnTheirTurn() {
        $this->hangman->play($this->playerOne, $this->getProposition('A'));

        $this->assertFalse($this->hangman->canPlay($this->playerOne));
        $this->assertTrue($this->hangman->canPlay($this->playerTwo));

        $this->hangman->play($this->playerTwo, $this->getProposition('A'));

        $this->assertTrue($this->hangman->canPlay($this->playerOne));
        $this->assertFalse($this->hangman->canPlay($this->playerTwo));
    }

    /**
     * @test
     */
    public function testGetPlayers()
    {
        $players = array(
            $this->playerOne,
            $this->playerTwo
        );

        $hangman = new Hangman(self::ID, 'word', $players);
        $this->assertEquals($players, $hangman->getPlayers());
    }
}