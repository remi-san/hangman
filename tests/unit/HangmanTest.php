<?php
namespace Hangman\Test;

use Hangman\Entity\Hangman;
use Hangman\Result\HangmanBadProposition;
use Hangman\Result\HangmanGoodProposition;
use Hangman\Result\HangmanLost;
use Hangman\Test\Mock\HangmanMocker;
use Hangman\Test\Mock\PlayerOptionsMock;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\Player;
use MiniGame\Entity\PlayerId;
use MiniGame\Exceptions\IllegalMoveException;
use MiniGame\Exceptions\NotPlayerTurnException;
use MiniGame\Test\Mock\GameObjectMocker;
use Mockery\MockInterface;
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

        $this->playerOne = $this->getHangmanPlayer($this->playerOneId, self::P1_NAME);
        $this->playerOne->shouldReceive('setGame');
        $this->playerOne->shouldReceive('registerAggregateRoot');
        $this->playerOne->shouldReceive('handleRecursively');

        $this->playerTwo = $this->getHangmanPlayer($this->playerTwoId, self::P2_NAME);
        $this->playerTwo->shouldReceive('setGame');
        $this->playerTwo->shouldReceive('registerAggregateRoot');
        $this->playerTwo->shouldReceive('handleRecursively');

        $this->hangman = Hangman::createGame(
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
        $this->assertEquals($this->hangmanId, $this->hangman->getAggregateRootId());
        $this->assertEquals($this->hangmanId, $this->hangman->getId());
        $this->assertEquals($this->playerOne, $this->hangman->getCurrentPlayer());
    }

    /**
     * @test
     */
    public function testUuidIsGenerated()
    {
        $hangman = Hangman::createGame(null, self::WORD);
        $this->assertTrue(Uuid::isValid($hangman->getId()->getId()));
    }

    /**
     * @test
     */
    public function testCannotStartAGameWithNoPlayer()
    {
        $hangman = Hangman::createGame(null, self::WORD);

        $this->setExpectedException('\Hangman\Exception\HangmanException');

        $hangman->startGame();
    }

    /**
     * @test
     */
    public function testCannotStartAGameTwice()
    {
        $hangman = Hangman::createGame(null, self::WORD, array($this->playerOne));
        $hangman->startGame();

        $this->setExpectedException('\Hangman\Exception\HangmanException');

        $hangman->startGame();
    }

    /**
     * @test
     */
    public function testCannotAddAPlayerOnceGameStarted()
    {
        $this->hangman->startGame();

        $this->setExpectedException('\Hangman\Exception\HangmanException');

        $hangmanPlayerOptions = \Mockery::mock('\Hangman\Options\HangmanPlayerOptions', function (MockInterface $mock) {
            $mock->shouldReceive('getPlayerId')->andReturn(new PlayerId(42))->byDefault();
            $mock->shouldReceive('getName')->andReturn('toto')->byDefault();
            $mock->shouldReceive('getLives')->andReturn(6)->byDefault();
        });

        $this->hangman->addPlayerToGame($hangmanPlayerOptions);
    }

    /**
     * @test
     */
    public function testAddAPlayerWithIllegalOptions()
    {
        $hangman = Hangman::createGame(null, self::WORD);

        $this->setExpectedException('\Hangman\Exception\HangmanException');

        $hangmanPlayerOptions = \Mockery::mock('\MiniGame\PlayerOptions');
        $hangman->addPlayerToGame($hangmanPlayerOptions);

    }

    /**
     * @test
     */
    public function testAddAPlayer()
    {
        $hangman = Hangman::createGame(null, self::WORD);

        $this->assertEquals(0, count($hangman->getPlayers()));

        $hangmanPlayerOptions = \Mockery::mock('\Hangman\Options\HangmanPlayerOptions', function (MockInterface $mock) {
            $mock->shouldReceive('getPlayerId')->andReturn(new PlayerId(42))->byDefault();
            $mock->shouldReceive('getName')->andReturn('toto')->byDefault();
            $mock->shouldReceive('getLives')->andReturn(6)->byDefault();
        });
        $hangman->addPlayerToGame($hangmanPlayerOptions);

        $this->assertEquals(1, count($hangman->getPlayers()));
    }

    /**
     * @test
     */
    public function testPlayBeforeGamestarted()
    {
        $this->setExpectedException('\MiniGame\Exceptions\InactiveGameException');

        $this->hangman->play($this->playerOneId, $this->getProposition('A'));
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
        $this->playerOne->shouldReceive('getRemainingLives')->andReturn(self::CHANCES - 1);
        $this->playerOne->shouldReceive('getPlayedLetters')->andReturn(array());
        $this->playerOne->shouldReceive('playLetter');

        $this->setExpectedException('\\MiniGame\\Exceptions\\IllegalMoveException');

        $this->hangman->startGame();

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
        $this->playerOne->shouldReceive('getPlayedLetters')->andReturn(array($letter=>$letter));
        $this->playerOne->shouldReceive('getRemainingLives')->andReturn(self::CHANCES);

        $this->hangman->startGame();

        /* @var $feedback HangmanGoodProposition */
        $feedback = $this->hangman->play($this->playerOneId, $this->getProposition($letter));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanGoodProposition', $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(array('H' => 'H'), $feedback->getLettersPlayed());
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
        $this->playerOne->shouldReceive('getPlayedLetters')->andReturn(array($letter=>$letter));
        $this->playerOne->shouldReceive('getRemainingLives')->andReturn(self::CHANCES);

        $this->hangman->startGame();

        /* @var $feedback HangmanBadProposition */
        $feedback = $this->hangman->play($this->playerOneId, $this->getProposition($letter));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanBadProposition', $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(array('Z'=>'Z'), $feedback->getLettersPlayed());
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
        $this->playerOne->shouldReceive('getPlayedLetters')->andReturn(array());
        $this->playerOne->shouldReceive('getRemainingLives')->andReturn(self::CHANCES - 1);
        $this->playerOne->shouldReceive('playLetter');

        $this->setExpectedException('\\MiniGame\\Exceptions\\IllegalMoveException');
        $move = $this->getAnswer('ABCD');

        $this->hangman->startGame();

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

        $this->hangman->startGame();

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
        $hangman = Hangman::createGame(
            $this->hangmanId,
            $word,
            array($this->playerOne, $this->playerTwo),
            self::CHANCES
        );

        $hangman->startGame();

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
        $this->playerOne->shouldReceive('getRemainingLives')->andReturn(1, 0);

        $hangman = Hangman::createGame($this->hangmanId, self::WORD, array($this->playerOne, $this->playerTwo), 1);

        $hangman->startGame();

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

        $this->hangman->startGame();

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

        $this->hangman->startGame();

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

        $hangman = Hangman::createGame($this->hangmanId, 'word', $players);
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

        $hangman = Hangman::createGame($this->hangmanId, 'word', $players);
        $this->assertNull($hangman->getPlayer($this->getPlayerId(999)));
    }
}
