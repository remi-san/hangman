<?php
namespace Hangman\Test;

use Hangman\Entity\Hangman;
use Hangman\Options\HangmanPlayerOptions;
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
     * @var HangmanPlayerOptions
     */
    protected $playerOne;

    /**
     * @var PlayerId
     */
    protected $playerOneId;

    /**
     * @var HangmanPlayerOptions
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

        $this->playerOne = new HangmanPlayerOptions($this->playerOneId, $this->hangmanId, self::P1_NAME, self::CHANCES);

        $this->playerTwo = new HangmanPlayerOptions($this->playerTwoId, $this->hangmanId, self::P2_NAME, self::CHANCES);

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
        $this->assertEquals($this->playerOne->getPlayerId(), $this->hangman->getCurrentPlayer()->getId());
    }

    /**
     * @test
     */
    public function testReconstitution()
    {
        $this->assertTrue(Hangman::instantiateForReconstitution() instanceof Hangman);
    }

    /**
     * @test
     */
    public function testCannotStartAGameWithNoPlayer()
    {
        $hangman = Hangman::createGame($this->hangmanId, self::WORD);

        $this->setExpectedException('\Hangman\Exception\HangmanException');

        $hangman->startGame();
    }

    /**
     * @test
     */
    public function testCannotStartAGameTwice()
    {
        $hangman = Hangman::createGame($this->hangmanId, self::WORD, array($this->playerOne));
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
            $mock->shouldReceive('getExternalReference')->andReturn('ext-ref')->byDefault();
        });

        $this->hangman->addPlayerToGame($hangmanPlayerOptions);
    }

    /**
     * @test
     */
    public function testAddAPlayerWithIllegalOptions()
    {
        $hangman = Hangman::createGame($this->hangmanId, self::WORD);

        $this->setExpectedException('\Hangman\Exception\HangmanPlayerOptionsException');

        $hangmanPlayerOptions = \Mockery::mock('\MiniGame\PlayerOptions');
        $hangmanPlayerOptions->shouldReceive('getPlayerId')->andReturn(new PlayerId(42));
        $hangman->addPlayerToGame($hangmanPlayerOptions);

    }

    /**
     * @test
     */
    public function testAddAPlayer()
    {
        $hangman = Hangman::createGame($this->hangmanId, self::WORD);

        $this->assertEquals(0, count($hangman->getPlayers()));

        $hangmanPlayerOptions = \Mockery::mock('\Hangman\Options\HangmanPlayerOptions', function (MockInterface $mock) {
            $mock->shouldReceive('getPlayerId')->andReturn(new PlayerId(42))->byDefault();
            $mock->shouldReceive('getName')->andReturn('toto')->byDefault();
            $mock->shouldReceive('getLives')->andReturn(6)->byDefault();
            $mock->shouldReceive('getExternalReference')->andReturn('ext-ref')->byDefault();
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

        $this->hangman->startGame();

        /* @var $feedback HangmanGoodProposition */
        $feedback = $this->hangman->play($this->playerOneId, $this->getProposition($letter));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanGoodProposition', $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(array('H' => 'H'), $feedback->getPlayedLetters());
        $this->assertEquals(self::CHANCES, $feedback->getRemainingLives());
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

        $this->hangman->startGame();

        /* @var $feedback HangmanBadProposition */
        $feedback = $this->hangman->play($this->playerOneId, $this->getProposition($letter));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanBadProposition', $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(array('Z'=>'Z'), $feedback->getPlayedLetters());
        $this->assertEquals(self::CHANCES-1, $feedback->getRemainingLives());
        $this->assertEquals('_ _ _ _ _ _ _ _ _ _ _', $feedback->getFeedBack());

        $this->assertFalse($this->hangman->canPlayerPlay($this->playerOneId));
        $this->assertTrue($this->hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function testPlayerOnePlaysIllegalAnswer()
    {
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
        $this->hangman->startGame();

        /* @var $feedback \Hangman\Result\HangmanWon */
        $feedback = $this->hangman->play($this->playerOneId, $this->getAnswer(self::WORD));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanWon', $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(array(), $feedback->getPlayedLetters());
        $this->assertEquals(self::CHANCES, $feedback->getRemainingLives());
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
        $this->assertEquals(array('A'=>'A'), $feedback->getPlayedLetters());
        $this->assertEquals(self::CHANCES, $feedback->getRemainingLives());
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

        $playerOne = new HangmanPlayerOptions($this->playerOneId, $this->hangmanId, self::P1_NAME, 1);

        $hangman = Hangman::createGame($this->hangmanId, self::WORD, array($playerOne, $this->playerTwo));

        $hangman->startGame();

        /* @var $feedback \Hangman\Result\HangmanLost */
        $feedback = $hangman->play($this->playerOneId, $this->getProposition($letter));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanLost', $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(array('Z'=>'Z'), $feedback->getPlayedLetters());
        $this->assertEquals(0, $feedback->getRemainingLives());
        $this->assertEquals(self::WORD, $feedback->getSolution());

        $this->assertFalse($hangman->canPlayerPlay($this->playerOneId));
        $this->assertFalse($hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function testPlayerOneBadSolution()
    {
        $this->hangman->startGame();

        /* @var $feedback HangmanLost */
        $feedback = $this->hangman->play($this->playerOneId, $this->getAnswer('HHHHHHHHHHH'));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanLost', $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(array(), $feedback->getPlayedLetters());
        $this->assertEquals(self::CHANCES, $feedback->getRemainingLives());
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
        $retrievedPlayers = $hangman->getPlayers();
        $this->assertEquals(2, count($retrievedPlayers));
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
