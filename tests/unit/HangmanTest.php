<?php
namespace Hangman\Test;

use Hangman\Entity\Hangman;
use Hangman\Event\HangmanGameFailedStartingEvent;
use Hangman\Event\HangmanGameLostEvent;
use Hangman\Event\HangmanGameStartedEvent;
use Hangman\Event\HangmanPlayerCreatedEvent;
use Hangman\Event\HangmanPlayerFailedCreatingEvent;
use Hangman\Event\HangmanPlayerProposedInvalidAnswerEvent;
use Hangman\Event\HangmanPlayerTriedPlayingDuringAnotherPlayerTurnEvent;
use Hangman\Event\HangmanPlayerTriedPlayingInactiveGameEvent;
use Hangman\Options\HangmanPlayerOptions;
use Hangman\Result\HangmanBadProposition;
use Hangman\Result\HangmanGoodProposition;
use Hangman\Result\HangmanLost;
use Hangman\Test\Mock\HangmanMocker;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\Test\Mock\GameObjectMocker;

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

        $this->playerOneId = PlayerId::create(self::P1_ID);
        $this->playerTwoId = PlayerId::create(self::P2_ID);

        $this->playerOne = HangmanPlayerOptions::create($this->playerOneId, $this->hangmanId, self::P1_NAME, self::CHANCES);

        $this->playerTwo = HangmanPlayerOptions::create($this->playerTwoId, $this->hangmanId, self::P2_NAME, self::CHANCES);

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

        $hangmanPlayerOptions = $this->getHangmanPlayerOptions(PlayerId::create(42), 'toto', 6, 'ext-ref');

        $result = $this->hangman->addPlayerToGame($hangmanPlayerOptions);

        $this->assertTrue($result instanceof HangmanPlayerFailedCreatingEvent);
    }

    /**
     * @test
     */
    public function testAddAPlayerWithIllegalOptions()
    {
        $hangman = Hangman::createGame($this->hangmanId, self::WORD);

        $this->setExpectedException('\Hangman\Exception\HangmanPlayerOptionsException');

        $hangmanPlayerOptions = $this->getPlayerOptions(PlayerId::create(42));

        $hangman->addPlayerToGame($hangmanPlayerOptions);

    }

    /**
     * @test
     */
    public function testAddAPlayer()
    {
        $hangman = Hangman::createGame($this->hangmanId, self::WORD);

        $this->assertEquals(0, count($hangman->getPlayers()));

        $hangmanPlayerOptions = $this->getHangmanPlayerOptions(PlayerId::create(42), 'toto', 6, 'ext-ref');

        $result = $hangman->addPlayerToGame($hangmanPlayerOptions);

        $this->assertTrue($result instanceof HangmanPlayerCreatedEvent);
        $this->assertEquals(1, count($hangman->getPlayers()));
    }

    /**
     * @test
     */
    public function testPlayBeforeGamestarted()
    {
        $result = $this->hangman->play($this->playerOneId, $this->getProposition('A'));

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
        $this->setExpectedException('\\MiniGame\\Exceptions\\IllegalMoveException');

        $this->hangman->startGame($this->playerOneId);

        /* @var $feedback HangmanGoodProposition */
        $this->hangman->play($this->playerOneId, $this->getMove('unknown'));
    }

    /**
     * @test
     */
    public function testPlayerOnePlaysOneGoodLetter()
    {
        $letter = 'H';

        $this->hangman->startGame($this->playerOneId);

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

        $this->hangman->startGame($this->playerOneId);

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
        $move = $this->getAnswer('ABCD');

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
        $hangman = Hangman::createGame($this->hangmanId, $word);

        $hangman->addPlayerToGame($this->playerOne);
        $hangman->addPlayerToGame($this->playerTwo);

        $hangman->startGame($this->playerOneId);

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
    public function testPlayerOneLosesAlone()
    {
        $letter = 'Z';
        $playerId = PlayerId::create(42);

        $playerOne = HangmanPlayerOptions::create($playerId, $this->hangmanId, self::P1_NAME, 1);

        $hangman = Hangman::createGame($this->hangmanId, self::WORD);

        $hangman->addPlayerToGame($playerOne);

        $hangman->startGame($playerId);

        /* @var $feedback \Hangman\Result\HangmanLost */
        $feedback = $hangman->play($playerId, $this->getProposition($letter));

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

        /* @var $feedback \Hangman\Result\HangmanLost */
        $feedback = $hangman->play($this->playerOneId, $this->getProposition($letter));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanLost', $feedback);
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

        /* @var $feedback HangmanLost */
        $feedback = $this->hangman->play($this->playerOneId, $this->getAnswer('HHHHHHHHHHH'));

        $this->assertInstanceOf('\\Hangman\\Result\\HangmanLost', $feedback);
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

        $result = $this->hangman->play($this->playerTwoId, $this->getProposition('A'));

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
        $this->assertNull($hangman->getPlayer($this->getPlayerId(999)));
    }
}
