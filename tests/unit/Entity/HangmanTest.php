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
use Hangman\Exception\HangmanException;
use Hangman\Exception\HangmanPlayerOptionsException;
use Hangman\Move\Answer;
use Hangman\Move\Proposition;
use Hangman\Options\HangmanPlayerOptions;
use Hangman\Result\HangmanBadProposition;
use Hangman\Result\HangmanGoodProposition;
use Hangman\Result\HangmanLost;
use Hangman\Result\HangmanWon;
use League\Event\EventInterface;
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
    protected $playerOneOptions;

    /** @var HangmanPlayerOptions */
    protected $playerTwoOptions;

    /** @var HangmanPlayerOptions */
    private $playerThreeOptions;

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

        $this->playerOneOptions = HangmanPlayerOptions::create(
            $this->playerOneId,
            $this->hangmanId,
            self::P1_NAME,
            self::CHANCES
        );
        $this->playerTwoOptions = HangmanPlayerOptions::create(
            $this->playerTwoId,
            $this->hangmanId,
            self::P2_NAME,
            self::CHANCES
        );
        $this->playerThreeOptions = HangmanPlayerOptions::create(
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
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldBeBuildable()
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
    public function itShouldReturnPlayers()
    {
        $this->givenTheGameHasTwoPlayers();

        $retrievedPlayers = $this->hangman->getPlayers();

        $this->assertEquals(2, count($retrievedPlayers));
    }

    /**
     * @test
     */
    public function itShouldReturnPlayer()
    {
        $this->givenTheGameHasTwoPlayers();

        $this->assertNotNull($this->playerOneOptions, $this->hangman->getPlayer($this->playerOneId));
        $this->assertNotNull($this->playerTwoOptions, $this->hangman->getPlayer($this->playerTwoId));
        $this->assertNull($this->hangman->getPlayer($this->playerThreeId));
    }

    /**
     * @test
     */
    public function itShouldBeAbleForAPlayerToLeaveWhenGameHasNotStarted()
    {
        $this->givenTheGameHasTwoPlayers();

        $return = $this->hangman->leaveGame($this->playerOneId);

        $this->assertNull($this->hangman->getPlayer($this->playerOneId));
        $this->assertNull($return);
    }

    /**
     * @test
     */
    public function itShouldMakePlayerLoseIfLeavingWhenGameHasStarted()
    {
        $this->givenTheGameHasTwoPlayers();
        $this->givenPlayerOneStartedTheGame();

        $return = $this->hangman->leaveGame($this->playerOneId);

        $this->assertInstanceOf(HangmanPlayerLostEvent::class, $return);
    }

    /**
     * @test
     */
    public function itShouldBeAbleForAPlayerToLeaveWhenGameIsOver()
    {
        $this->givenTheGameHasTwoPlayers();
        $this->givenPlayerOneStartedTheGame();
        $this->givenPlayerOneHasLost();

        $return = $this->hangman->leaveGame($this->playerOneId);

        $this->assertNull($return);
    }

    /**
     * @test
     */
    public function itShouldNotBePossibleToStartAGameWithNoPlayer()
    {
        $result = $this->hangman->startGame($this->playerOneId);

        $this->assertTrue($result instanceof HangmanGameFailedStartingEvent);
    }

    /**
     * @test
     */
    public function itShouldNotBePossibleToStartAGameTwice()
    {
        $this->givenTheGameHasTwoPlayers();

        $result = $this->hangman->startGame($this->playerOneId);
        $this->assertTrue($result instanceof HangmanGameStartedEvent);

        $result = $this->hangman->startGame($this->playerOneId);
        $this->assertTrue($result instanceof HangmanGameFailedStartingEvent);

        $result = $this->hangman->startGame($this->playerTwoId);
        $this->assertTrue($result instanceof HangmanGameFailedStartingEvent);
    }

    /**
     * @test
     */
    public function itShouldNotBePossibleToAddAPlayerOnceGameHasStarted()
    {
        $this->givenTheGameHasTwoPlayers();
        $this->givenPlayerOneStartedTheGame();

        $result = $this->hangman->addPlayerToGame($this->playerThreeOptions);
        $this->assertTrue($result instanceof HangmanPlayerFailedCreatingEvent);
    }

    /**
     * @test
     */
    public function itShouldNotBePossibleToAddAPlayerWithIllegalOptions()
    {
        $this->setExpectedException(HangmanPlayerOptionsException::class);

        $this->hangman->addPlayerToGame($this->invalidPlayerOptions);
    }

    /**
     * @test
     */
    public function itShouldAddAPlayer()
    {
        $result = $this->hangman->addPlayerToGame($this->playerOneOptions);

        $this->assertTrue($result instanceof HangmanPlayerCreatedEvent);
        $this->assertEquals(1, count($this->hangman->getPlayers()));
    }

    /**
     * @test
     */
    public function itShouldNotBePossibleToPlayBeforeGameHasStarted()
    {
        $this->givenTheGameHasTwoPlayers();

        $result = $this->hangman->play($this->playerOneId, Proposition::create('A'));

        $this->assertTrue($result instanceof HangmanPlayerTriedPlayingInactiveGameEvent);
    }

    /**
     * @test
     */
    public function itShouldSetTheForstPlayerToTheOneThatStartedTheGame()
    {
        $this->givenTheGameHasTwoPlayers();

        $result = $this->hangman->startGame($this->playerOneId);
        $this->assertInstanceOf(HangmanGameStartedEvent::class, $result);

        $this->assertTrue($this->hangman->canPlayerPlay($this->playerOneId));
        $this->assertFalse($this->hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function itShouldNotBePossibleToPlayWithUnknownMove()
    {
        $this->givenTheGameHasTwoPlayers();
        $this->givenPlayerOneStartedTheGame();

        $this->setExpectedException(IllegalMoveException::class);

        $this->hangman->play($this->playerOneId, $this->move);
    }

    /**
     * @test
     */
    public function itShouldPlayOneGoodLetter()
    {
        $presentLetter = 'H';

        $this->givenTheGameHasTwoPlayers();
        $this->givenPlayerOneStartedTheGame();

        /* @var HangmanGoodProposition $feedback */
        $feedback = $this->hangman->play($this->playerOneId, Proposition::create($presentLetter));

        $this->assertInstanceOf(HangmanGoodProposition::class, $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals([$presentLetter], $feedback->getPlayedLetters());
        $this->assertEquals(self::CHANCES, $feedback->getRemainingLives());
        $this->assertEquals('H _ _ _ H H _ _ _ _ _', $feedback->getFeedBack());

        $this->assertFalse($this->hangman->canPlayerPlay($this->playerOneId));
        $this->assertTrue($this->hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function itShouldPlayOneBadLetter()
    {
        $absentLetter = 'Z';

        $this->givenTheGameHasTwoPlayers();
        $this->givenPlayerOneStartedTheGame();

        /* @var HangmanBadProposition $feedback */
        $feedback = $this->hangman->play($this->playerOneId, Proposition::create($absentLetter));

        $this->assertInstanceOf(HangmanBadProposition::class, $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals([$absentLetter], $feedback->getPlayedLetters());
        $this->assertEquals(self::CHANCES-1, $feedback->getRemainingLives());
        $this->assertEquals('_ _ _ _ _ _ _ _ _ _ _', $feedback->getFeedBack());

        $this->assertFalse($this->hangman->canPlayerPlay($this->playerOneId));
        $this->assertTrue($this->hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function itShouldNotBePossibleToPlayIllegalAnswer()
    {
        $illegalAnswer = Answer::create('ABCD');

        $this->givenTheGameHasTwoPlayers();
        $this->givenPlayerOneStartedTheGame();

        $result = $this->hangman->play($this->playerOneId, $illegalAnswer);

        $this->assertTrue($result instanceof HangmanPlayerProposedInvalidAnswerEvent);
        $this->assertTrue($this->hangman->canPlayerPlay($this->playerOneId));
        $this->assertFalse($this->hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function itShouldFindSolution()
    {
        $this->givenTheGameHasTwoPlayers();
        $this->givenPlayerOneStartedTheGame();

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
    public function itShouldFindLastLetter()
    {
        $letter = 'A';

        $this->givenAGameWithTheSameFourLettersWord($letter);
        $this->givenTheGameHasTwoPlayers();
        $this->givenPlayerOneStartedTheGame();

        /* @var HangmanWon $feedback */
        $feedback = $this->hangman->play($this->playerOneId, Proposition::create($letter));

        $this->assertInstanceOf(HangmanWon::class, $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals([$letter], $feedback->getPlayedLetters());
        $this->assertEquals(self::CHANCES, $feedback->getRemainingLives());
        $this->assertEquals($letter . $letter . $letter . $letter, $feedback->getSolution());

        $this->assertFalse($this->hangman->canPlayerPlay($this->playerOneId));
        $this->assertFalse($this->hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function itShouldLetPlayerOneLoseAlone()
    {
        $badLetter = 'Z';
        $this->givenTheGameHasAPlayerWithOneLifeLeft();
        $this->givenPlayerOneStartedTheGame();

        /* @var HangmanGameLostEvent $feedback */
        $feedback = $this->hangman->play($this->playerOneId, Proposition::create($badLetter));

        $this->assertInstanceOf(HangmanGameLostEvent::class, $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(self::WORD, $feedback->getSolution());

        $this->assertFalse($this->hangman->canPlayerPlay($this->playerOneId));
    }

    /**
     * @test
     */
    public function itShouldLetPlayerOneLose()
    {
        $letter = 'Z';
        $this->givenTheGameHasAPlayerWithOneLifeLeft();
        $this->givenTheGameHasASecondPlayer();
        $this->givenPlayerOneStartedTheGame();

        /* @var HangmanLost $feedback */
        $feedback = $this->hangman->play($this->playerOneId, Proposition::create($letter));

        $this->assertInstanceOf(HangmanLost::class, $feedback);
        $this->assertEquals($this->playerOneId, $feedback->getPlayerId());
        $this->assertEquals(['Z'], $feedback->getPlayedLetters());
        $this->assertEquals(0, $feedback->getRemainingLives());
        $this->assertEquals(self::WORD, $feedback->getSolution());

        $this->assertFalse($this->hangman->canPlayerPlay($this->playerOneId));
        $this->assertTrue($this->hangman->canPlayerPlay($this->playerTwoId));
    }

    /**
     * @test
     */
    public function itShouldLetPlayerOneLoseProvidingBadSolution()
    {
        $this->givenTheGameHasTwoPlayers();
        $this->givenPlayerOneStartedTheGame();

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
    public function itShouldNotBePossibleForPlayerToPlayPropositionWhenNotHisTurn()
    {
        $this->givenTheGameHasTwoPlayers();
        $this->givenPlayerOneStartedTheGame();

        $result = $this->hangman->play($this->playerTwoId, Proposition::create('A'));

        $this->assertTrue($result instanceof HangmanPlayerTriedPlayingDuringAnotherPlayerTurnEvent);
        $this->assertTrue($this->hangman->canPlayerPlay($this->playerOneId));
    }

    /**
     * @test
     */
    public function itShouldNotBePossibleForPlayerToPlayAnswerWhenNotHisTurn()
    {
        $this->givenTheGameHasTwoPlayers();
        $this->givenPlayerOneStartedTheGame();

        $result = $this->hangman->play($this->playerTwoId, Answer::create('A'));

        $this->assertTrue($result instanceof HangmanPlayerTriedPlayingDuringAnotherPlayerTurnEvent);
        $this->assertTrue($this->hangman->canPlayerPlay($this->playerOneId));
    }

    /**
     * @test
     */
    public function itShouldBePossibleToBuildTheGameForReconstitution()
    {
        $this->assertTrue(Hangman::instantiateForReconstitution() instanceof Hangman);
    }

    /**
     * @test
     */
    public function itShouldNotBePossibleToApplyAnUnsupportedEvent()
    {
        $this->setExpectedException(HangmanException::class);

        $this->hangman->apply(\Mockery::mock(EventInterface::class));
    }

    private function givenTheGameHasTwoPlayers()
    {
        $this->givenTheGameHasAFirstPlayer();
        $this->givenTheGameHasASecondPlayer();
    }

    private function givenTheGameHasAFirstPlayer()
    {
        $this->hangman->addPlayerToGame($this->playerOneOptions);
    }

    private function givenTheGameHasASecondPlayer()
    {
        $this->hangman->addPlayerToGame($this->playerTwoOptions);
    }

    private function givenPlayerOneStartedTheGame()
    {
        $this->hangman->startGame($this->playerOneId);
    }

    private function givenPlayerOneHasLost()
    {
        $this->hangman->play($this->playerOneId, Answer::create('ASS--KICKER'));
    }

    private function givenTheGameHasAPlayerWithOneLifeLeft()
    {
        $this->playerOneOptions = HangmanPlayerOptions::create($this->playerOneId, $this->hangmanId, self::P1_NAME, 1);
        $this->hangman->addPlayerToGame($this->playerOneOptions);
    }

    /**
     * @param string $letter
     *
     * @return string
     */
    private function givenAGameWithTheSameFourLettersWord($letter)
    {
        $word = $letter . $letter . $letter . $letter;
        $this->hangman = Hangman::createGame($this->hangmanId, $word);
        return $word;
    }
}
