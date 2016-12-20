<?php
namespace Hangman\Test\Entity;

use Hangman\Entity\Hangman;
use Hangman\Event\HangmanGameFailedStartingEvent;
use Hangman\Event\HangmanGameLostEvent;
use Hangman\Event\HangmanGameStartedEvent;
use Hangman\Event\HangmanPlayerCreatedEvent;
use Hangman\Event\HangmanPlayerFailedCreatingEvent;
use Hangman\Event\HangmanPlayerLostEvent;
use Hangman\Event\HangmanPlayerProposedInvalidAnswerEvent;
use Hangman\Event\HangmanPlayerTriedPlayingDuringAnotherPlayerTurnEvent;
use Hangman\Event\HangmanPlayerTriedPlayingInactiveGameEvent;
use Hangman\Exception\HangmanPlayerOptionsException;
use Hangman\Move\Answer;
use Hangman\Move\Proposition;
use Hangman\Options\HangmanPlayerOptions;
use Hangman\Result\HangmanBadProposition;
use Hangman\Result\HangmanGoodProposition;
use Hangman\Result\HangmanLost;
use Hangman\Result\HangmanWon;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\Exceptions\IllegalMoveException;
use MiniGame\Move;
use MiniGame\PlayerOptions;
use Mockery\Mock;

class HangmanTest extends \PHPUnit_Framework_TestCase
{
    const WORD = 'HITCHHICKER';
    const ID = 42;
    const CHANCES = 5;
    
    const P1_ID = 314;
    const P1_NAME = 'John';
    
    const P2_ID = 666;
    const P2_NAME = 'James';

    const P3_ID = 999;
    const P3_NAME = 'Jack';
    
    /** @var MiniGameId */
    protected $hangmanId;

    /** @var Hangman */
    protected $hangman;

    /** @var PlayerId */
    protected $playerOneId;

    /** @var PlayerId */
    protected $playerTwoId;

    /** @var PlayerId */
    private $playerThreeId;

    /** @var HangmanPlayerOptions */
    protected $playerOne;

    /** @var HangmanPlayerOptions */
    protected $playerTwo;

    /** @var HangmanPlayerOptions */
    private $playerThree;

    /** @var PlayerOptions | Mock */
    private $invalidPlayerOptions;

    /** @var Move */
    private $move;

    public function setUp()
    {
        $this->hangmanId = MiniGameId::create(self::ID);

        $this->playerOneId = PlayerId::create(self::P1_ID);
        $this->playerTwoId = PlayerId::create(self::P2_ID);
        $this->playerThreeId = PlayerId::create(self::P3_ID);

        $this->invalidPlayerOptions = \Mockery::mock(PlayerOptions::class);
        $this->invalidPlayerOptions->shouldReceive('getPlayerId')->andReturn($this->playerThreeId);
        $this->invalidPlayerOptions->shouldReceive('getGameId')->andReturn($this->hangmanId);
        $this->invalidPlayerOptions->shouldReceive('getName')->andReturn(self::P3_NAME);

        $this->playerOne = HangmanPlayerOptions::create(
            $this->playerOneId,
            $this->hangmanId,
            self::P1_NAME,
            self::CHANCES
        );
        $this->playerTwo = HangmanPlayerOptions::create(
            $this->playerTwoId,
            $this->hangmanId,
            self::P2_NAME,
            self::CHANCES
        );
        $this->playerThree = HangmanPlayerOptions::create(
            $this->playerThreeId,
            $this->hangmanId,
            self::P3_NAME,
            self::CHANCES
        );

        $this->move = \Mockery::mock(Move::class);

        $this->hangman = Hangman::createGame(
            $this->hangmanId,
            self::WORD
        );
        $this->hangman->addPlayerToGame($this->playerOne);
        $this->hangman->addPlayerToGame($this->playerTwo);
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
        $this->assertNull($this->hangman->getCurrentPlayer());
        $this->assertNull($this->hangman->getPlayer(null));
    }

    /**
     * @test
     */
    public function testLeaveWhenNotStarted()
    {
        $return = $this->hangman->leaveGame($this->playerOneId);

        $this->assertNull($this->hangman->getPlayer($this->playerOneId));
        $this->assertNull($return);
    }

    /**
     * @test
     */
    public function testLeaveWhenStarted()
    {
        $this->hangman->startGame($this->playerOneId);
        $return = $this->hangman->leaveGame($this->playerOneId);

        $this->assertInstanceOf(HangmanPlayerLostEvent::class, $return);
    }

    /**
     * @test
     */
    public function testLeaveWhenOver()
    {
        $this->hangman->startGame($this->playerOneId);
        $this->hangman->play($this->playerOneId, Answer::create('ASS--KICKER'));
        $return = $this->hangman->leaveGame($this->playerOneId);

        $this->assertNull($return);
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

        $result = $hangman->startGame($this->playerOneId);

        $this->assertTrue($result instanceof HangmanGameFailedStartingEvent);
    }

    /**
     * @test
     */
    public function testCannotStartAGameTwice()
    {
        $hangman = Hangman::createGame($this->hangmanId, self::WORD);
        $hangman->addPlayerToGame($this->playerOne);
        $result = $hangman->startGame($this->playerOneId);

        $this->assertTrue($result instanceof HangmanGameStartedEvent);

        $result = $hangman->startGame($this->playerOneId);

        $this->assertTrue($result instanceof HangmanGameFailedStartingEvent);
    }

    /**
     * @test
     */
    public function testCannotAddAPlayerOnceGameStarted()
    {
        $this->hangman->startGame($this->playerOneId);

        $result = $this->hangman->addPlayerToGame($this->playerThree);

        $this->assertTrue($result instanceof HangmanPlayerFailedCreatingEvent);
    }

    /**
     * @test
     */
    public function testAddAPlayerWithIllegalOptions()
    {
        $hangman = Hangman::createGame($this->hangmanId, self::WORD);

        $this->setExpectedException(HangmanPlayerOptionsException::class);

        $hangman->addPlayerToGame($this->invalidPlayerOptions);

    }

    /**
     * @test
     */
    public function testAddAPlayer()
    {
        $hangman = Hangman::createGame($this->hangmanId, self::WORD);

        $this->assertEquals(0, count($hangman->getPlayers()));

        $result = $hangman->addPlayerToGame($this->playerOne);

        $this->assertTrue($result instanceof HangmanPlayerCreatedEvent);
        $this->assertEquals(1, count($hangman->getPlayers()));
    }

    /**
     * @test
     */
    public function testPlayBeforeGamestarted()
    {
        $result = $this->hangman->play($this->playerOneId, Proposition::create('A'));

        $this->assertTrue($result instanceof HangmanPlayerTriedPlayingInactiveGameEvent);
    }

    /**
     * @test
     */
    public function testCanPlay()
    {
        $this->hangman->startGame($this->playerOneId);

        $this->assertTrue($this->hangman->canPlayerPlay($this->playerOneId));
        $this->assertFalse($this->hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function testPlayerOnePlaysWithUnknownMove()
    {
        $this->setExpectedException(IllegalMoveException::class);

        $this->hangman->startGame($this->playerOneId);

        /* @var HangmanGoodProposition $feedback */
        $this->hangman->play($this->playerOneId, $this->move);
    }

    /**
     * @test
     */
    public function testPlayerOnePlaysOneGoodLetter()
    {
        $letter = 'H';

        $this->hangman->startGame($this->playerOneId);

        /* @var HangmanGoodProposition $feedback */
        $feedback = $this->hangman->play($this->playerOneId, Proposition::create($letter));

        $this->assertInstanceOf(HangmanGoodProposition::class, $feedback);
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

        $this->hangman->startGame($this->playerOneId);

        /* @var HangmanBadProposition $feedback */
        $feedback = $this->hangman->play($this->playerOneId, Proposition::create($letter));

        $this->assertInstanceOf(HangmanBadProposition::class, $feedback);
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
        $move = Answer::create('ABCD');

        $this->hangman->startGame($this->playerOneId);

        $result = $this->hangman->play($this->playerOneId, $move);

        $this->assertTrue($result instanceof HangmanPlayerProposedInvalidAnswerEvent);
        $this->assertTrue($this->hangman->canPlayerPlay($this->playerOneId));
        $this->assertFalse($this->hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function testPlayerOneFindsSolution()
    {
        $this->hangman->startGame($this->playerOneId);

        /* @var HangmanWon $feedback */
        $feedback = $this->hangman->play($this->playerOneId, Answer::create(self::WORD));

        $this->assertInstanceOf(HangmanWon::class, $feedback);
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

        /* @var HangmanWon $feedback */
        $hangman = Hangman::createGame($this->hangmanId, $word);

        $hangman->addPlayerToGame($this->playerOne);
        $hangman->addPlayerToGame($this->playerTwo);

        $hangman->startGame($this->playerOneId);

        $feedback = $hangman->play($this->playerOneId, Proposition::create($letter));

        $this->assertInstanceOf(HangmanWon::class, $feedback);
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
    public function testPlayerOneLosesAlone()
    {
        $letter = 'Z';
        $playerId = PlayerId::create(42);

        $playerOne = HangmanPlayerOptions::create($playerId, $this->hangmanId, self::P1_NAME, 1);

        $hangman = Hangman::createGame($this->hangmanId, self::WORD);

        $hangman->addPlayerToGame($playerOne);

        $hangman->startGame($playerId);

        /* @var HangmanGameLostEvent $feedback */
        $feedback = $hangman->play($playerId, Proposition::create($letter));

        $this->assertInstanceOf(HangmanGameLostEvent::class, $feedback);
        $this->assertEquals($playerId, $feedback->getPlayerId());
        $this->assertEquals(self::WORD, $feedback->getSolution());

        $this->assertFalse($hangman->canPlayerPlay($playerId));
    }

    /**
     * @test
     */
    public function testPlayerOneLoses()
    {
        $letter = 'Z';

        $playerOne = HangmanPlayerOptions::create($this->playerOneId, $this->hangmanId, self::P1_NAME, 1);

        $hangman = Hangman::createGame($this->hangmanId, self::WORD);

        $hangman->addPlayerToGame($playerOne);
        $hangman->addPlayerToGame($this->playerTwo);

        $hangman->startGame($this->playerOneId);

        /* @var HangmanLost $feedback */
        $feedback = $hangman->play($this->playerOneId, Proposition::create($letter));

        $this->assertInstanceOf(HangmanLost::class, $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(array('Z'=>'Z'), $feedback->getPlayedLetters());
        $this->assertEquals(0, $feedback->getRemainingLives());
        $this->assertEquals(self::WORD, $feedback->getSolution());

        $this->assertFalse($hangman->canPlayerPlay($this->playerOneId));
        $this->assertTrue($hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function testPlayerOneBadSolution()
    {
        $this->hangman->startGame($this->playerOneId);

        /* @var HangmanLost $feedback */
        $feedback = $this->hangman->play($this->playerOneId, Answer::create('HHHHHHHHHHH'));

        $this->assertInstanceOf(HangmanLost::class, $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(array(), $feedback->getPlayedLetters());
        $this->assertEquals(self::CHANCES, $feedback->getRemainingLives());
        $this->assertEquals(self::WORD, $feedback->getSolution());

        $this->assertFalse($this->hangman->canPlayerPlay($this->playerOneId));
        $this->assertTrue($this->hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function testPlayerTwoPlaysWhenNotHisTurn()
    {
        $this->hangman->startGame($this->playerOneId);

        $result = $this->hangman->play($this->playerTwoId, Proposition::create('A'));

        $this->assertTrue($result instanceof HangmanPlayerTriedPlayingDuringAnotherPlayerTurnEvent);
        $this->assertTrue($this->hangman->canPlayerPlay($this->playerOneId));
    }

    /**
     * @test
     */
    public function testPlayerTwoPlaysAnswerWhenNotHisTurn()
    {
        $this->hangman->startGame($this->playerOneId);

        $result = $this->hangman->play($this->playerTwoId, Answer::create('A'));

        $this->assertTrue($result instanceof HangmanPlayerTriedPlayingDuringAnotherPlayerTurnEvent);
        $this->assertTrue($this->hangman->canPlayerPlay($this->playerOneId));
    }

    /**
     * @test
     */
    public function testGetPlayers()
    {
        $hangman = Hangman::createGame($this->hangmanId, 'word');
        $hangman->addPlayerToGame($this->playerOne);
        $hangman->addPlayerToGame($this->playerTwo);
        $retrievedPlayers = $hangman->getPlayers();
        $this->assertEquals(2, count($retrievedPlayers));
    }

    /**
     * @test
     */
    public function testGetPlayer()
    {
        $hangman = Hangman::createGame($this->hangmanId, 'word');
        $hangman->addPlayerToGame($this->playerOne);
        $hangman->addPlayerToGame($this->playerTwo);
        $this->assertNull($hangman->getPlayer(PlayerId::create(999)));
    }
}
