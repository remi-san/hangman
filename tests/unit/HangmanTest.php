<?php
namespace Hangman\Test;

use Hangman\Entity\Hangman;
use Hangman\Result\HangmanBadProposition;
use Hangman\Result\HangmanGoodProposition;
use Hangman\Result\HangmanLost;
use Hangman\Test\Mock\HangmanMocker;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\Player;
use MiniGame\Entity\PlayerId;
use MiniGame\Exceptions\IllegalMoveException;
use MiniGame\Exceptions\NotPlayerTurnException;
use MiniGame\Test\Mock\GameObjectMocker;
use Rhumsaa\Uuid\Uuid;

class HangmanTest extends \PHPUnit_Framework_TestCase
{
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
     * @var MiniGameId
     */
    protected $hangmanId;

    /**
     * @var Hangman
     */
    protected $hangman;

    /**
     * @var Player
     */
    protected $playerOne;

    /**
     * @var PlayerId
     */
    protected $playerOneId;

    /**
     * @var Player
     */
    protected $playerTwo;

    /**
     * @var PlayerId
     */
    protected $playerTwoId;

    public function setUp()
    {
        $this->hangmanId = $this->getMiniGameId(self::ID);

        $this->playerOneId = $this->getPlayerId(self::P1_ID);
        $this->playerTwoId = $this->getPlayerId(self::P2_ID);

        $this->playerOne = $this->getPlayer($this->playerOneId, self::P1_NAME);
        $this->playerOne->shouldReceive('setGame');
        $this->playerTwo = $this->getPlayer($this->playerTwoId, self::P2_NAME);
        $this->playerTwo->shouldReceive('setGame');
        $this->hangman = new Hangman(
            $this->hangmanId,
            self::WORD,
            array($this->playerOne, $this->playerTwo),
            self::CHANCES
        );
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testGetters()
    {
        $this->assertEquals('HANGMAN', $this->hangman->getName());
        $this->assertEquals($this->hangmanId, $this->hangman->getId());
        $this->assertEquals($this->playerOne, $this->hangman->getCurrentPlayer());
    }

    /**
     * @test
     */
    public function testUuidIsGenerated()
    {
        $hangman = new Hangman(null, self::WORD);
        $this->assertTrue(Uuid::isValid($hangman->getId()->getId()));
    }

    /**
     * @test
     */
    public function testCanPlay()
    {
        $this->assertTrue($this->hangman->canPlayerPlay($this->playerOneId));
        $this->assertFalse($this->hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function testPlayerOnePlaysWithUnknownMove()
    {
        $this->playerOne->shouldReceive('loseLife')->once();
        $this->playerOne->shouldReceive('getRemainingLives')->andReturn(self::CHANCES - 1);
        $this->playerOne->shouldReceive('getPlayedLetters')->andReturn(array());

        $this->setExpectedException('\\MiniGame\\Exceptions\\IllegalMoveException');

        /* @var $feedback HangmanGoodProposition */
        $this->hangman->play($this->playerOneId, $this->getMove('unknown'));
    }

    /**
     * @test
     */
    public function testPlayerOnePlaysOneGoodLetter()
    {
        $letter = 'H';

        $this->playerOne->shouldReceive('playLetter')->with($letter)->once();
        $this->playerOne->shouldReceive('getPlayedLetters')->andReturn(array($letter));
        $this->playerOne->shouldReceive('getRemainingLives')->andReturn(self::CHANCES);

        /* @var $feedback HangmanGoodProposition */
        $feedback = $this->hangman->play($this->playerOneId, $this->getProposition($letter));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanGoodProposition', $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(array('H'), $feedback->getLettersPlayed());
        $this->assertEquals(self::CHANCES, $feedback->getRemainingChances());
        $this->assertEquals('H _ _ _ H H _ _ _ _ _', $feedback->getFeedBack());

        $this->assertFalse($this->hangman->canPlayerPlay($this->playerOneId));
        $this->assertTrue($this->hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function testPlayerOnePlaysOneBadLetter()
    {
        $letter = 'Z';

        $this->playerOne->shouldReceive('playLetter')->with($letter)->once();
        $this->playerOne->shouldReceive('getPlayedLetters')->andReturn(array($letter));
        $this->playerOne->shouldReceive('loseLife')->once();
        $this->playerOne->shouldReceive('getRemainingLives')->andReturn(self::CHANCES - 1);

        /* @var $feedback HangmanBadProposition */
        $feedback = $this->hangman->play($this->playerOneId, $this->getProposition($letter));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanBadProposition', $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(array('Z'), $feedback->getLettersPlayed());
        $this->assertEquals(self::CHANCES-1, $feedback->getRemainingChances());
        $this->assertEquals('_ _ _ _ _ _ _ _ _ _ _', $feedback->getFeedBack());

        $this->assertFalse($this->hangman->canPlayerPlay($this->playerOneId));
        $this->assertTrue($this->hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function testPlayerOnePlaysIllegalAnswer()
    {
        $this->playerOne->shouldReceive('loseLife')->once();
        $this->playerOne->shouldReceive('getPlayedLetters')->andReturn(array());
        $this->playerOne->shouldReceive('getRemainingLives')->andReturn(self::CHANCES - 1);

        $this->setExpectedException('\\MiniGame\\Exceptions\\IllegalMoveException');
        $move = $this->getAnswer('ABCD');

        try {
            $this->hangman->play($this->playerOneId, $move);
        } catch (IllegalMoveException $e) {
            $this->assertEquals($move, $e->getMove());

            $this->assertFalse($this->hangman->canPlayerPlay($this->playerOneId));
            $this->assertTrue($this->hangman->canPlayerPlay($this->playerTwoId));

            throw $e;
        }
    }

    /**
     * @test
     */
    public function testPlayerOneFindsSolution()
    {
        $this->playerOne->shouldReceive('getRemainingLives')->andReturn(self::CHANCES);
        $this->playerOne->shouldReceive('getPlayedLetters')->andReturn(array());

        /* @var $feedback \Hangman\Result\HangmanWon */
        $feedback = $this->hangman->play($this->playerOneId, $this->getAnswer(self::WORD));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanWon', $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(array(), $feedback->getLettersPlayed());
        $this->assertEquals(self::CHANCES, $feedback->getRemainingChances());
        $this->assertEquals(self::WORD, $feedback->getSolution());

        $this->assertFalse($this->hangman->canPlayerPlay($this->playerOneId));
        $this->assertFalse($this->hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function testPlayerOneFindsLastLetter()
    {
        $word = 'AAAA';
        $letter = 'A';

        $this->playerOne->shouldReceive('playLetter')->with($letter)->once();
        $this->playerOne->shouldReceive('getPlayedLetters')->andReturn(array($letter));
        $this->playerOne->shouldReceive('getRemainingLives')->andReturn(self::CHANCES);

        /* @var $feedback \Hangman\Result\HangmanWon */
        $hangman = new Hangman($this->hangmanId, $word, array($this->playerOne, $this->playerTwo), self::CHANCES);
        $feedback = $hangman->play($this->playerOneId, $this->getProposition($letter));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanWon', $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(array('A'), $feedback->getLettersPlayed());
        $this->assertEquals(self::CHANCES, $feedback->getRemainingChances());
        $this->assertEquals($word, $feedback->getSolution());

        $this->assertFalse($hangman->canPlayerPlay($this->playerOneId));
        $this->assertFalse($hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function testPlayerOneLoses()
    {
        $letter = 'Z';

        $this->playerOne->shouldReceive('playLetter')->with($letter)->once();
        $this->playerOne->shouldReceive('getPlayedLetters')->andReturn(array($letter));
        $this->playerOne->shouldReceive('loseLife')->once();
        $this->playerOne->shouldReceive('getRemainingLives')->andReturn(0);

        $hangman = new Hangman($this->hangmanId, self::WORD, array($this->playerOne, $this->playerTwo), 1);

        /* @var $feedback \Hangman\Result\HangmanLost */
        $feedback = $hangman->play($this->playerOneId, $this->getProposition($letter));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanLost', $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(array('Z'), $feedback->getLettersPlayed());
        $this->assertEquals(0, $feedback->getRemainingChances());
        $this->assertEquals(self::WORD, $feedback->getSolution());

        $this->assertFalse($hangman->canPlayerPlay($this->playerOneId));
        $this->assertFalse($hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function testPlayerOneBadSolution()
    {
        $this->playerOne->shouldReceive('getRemainingLives')->andReturn(self::CHANCES);
        $this->playerOne->shouldReceive('getPlayedLetters')->andReturn(array());

        /* @var $feedback HangmanLost */
        $feedback = $this->hangman->play($this->playerOneId, $this->getAnswer('HHHHHHHHHHH'));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanLost', $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(array(), $feedback->getLettersPlayed());
        $this->assertEquals(self::CHANCES, $feedback->getRemainingChances());
        $this->assertEquals(self::WORD, $feedback->getSolution());

        $this->assertFalse($this->hangman->canPlayerPlay($this->playerOneId));
        $this->assertFalse($this->hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function testPlayerTwoPlaysWhenNotHisTurn()
    {
        $this->setExpectedException('\\MiniGame\\Exceptions\\NotPlayerTurnException');
        try {
            $this->hangman->play($this->playerTwoId, $this->getProposition('A'));
        } catch (NotPlayerTurnException $e) {
            $this->assertEquals($this->playerTwoId, $e->getPlayerId());
            $this->assertEquals($this->hangmanId, $e->getMiniGameId());

            $this->assertTrue($this->hangman->canPlayerPlay($this->playerOneId));
            throw $e;
        }
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

        $hangman = new Hangman($this->hangmanId, 'word', $players);
        $this->assertEquals($players, $hangman->getPlayers());
    }

    /**
     * @test
     */
    public function testGetPlayer()
    {
        $players = array(
            $this->playerOne,
            $this->playerTwo
        );

        $hangman = new Hangman($this->hangmanId, 'word', $players);
        $this->assertNull($hangman->getPlayer($this->getPlayerId(999)));
    }
}
