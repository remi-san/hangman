<?php
namespace Hangman\Test\Entity;

use Hangman\Entity\Hangman;
use Hangman\Entity\HangmanPlayer;
use Hangman\Event\HangmanBadLetterProposedEvent;
use Hangman\Event\HangmanGoodLetterProposedEvent;
use Hangman\Event\HangmanPlayerLostEvent;
use Hangman\Event\HangmanPlayerWinEvent;
use Hangman\Test\Mock\TestableHangmanPlayer;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use Rhumsaa\Uuid\Uuid;

class HangmanPlayerTest extends \PHPUnit_Framework_TestCase
{
    /** @var PlayerId */
    private $playerId;

    /** @var PlayerId */
    private $otherPlayerId;

    /** @var string */
    private $name;

    /** @var int */
    private $lives;

    /** @var  MiniGameId */
    private $gameId;

    /** @var Hangman */
    private $game;

    /** @var string */
    private $externalReference;

    /** @var HangmanPlayer */
    private $player;

    /** @var string */
    private $firstLetter;

    /** @var string */
    private $secondLetter;

    /** @var int */
    private $livesLost;

    public function setUp()
    {
        $this->playerId = PlayerId::create(42);
        $this->otherPlayerId = PlayerId::create(43);
        $this->name = 'Douglas';
        $this->lives = 5;
        $this->gameId = MiniGameId::create(33);
        $this->game = Hangman::createGame($this->gameId, 'word');
        $this->externalReference = 'ext';

        $this->firstLetter = 'a';
        $this->secondLetter = 'b';

        $this->livesLost = 1;

        $this->player = new TestableHangmanPlayer(
            $this->playerId,
            $this->name,
            $this->lives,
            $this->game,
            $this->externalReference
        );
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldSetDefaultParameters()
    {
        $this->player = new HangmanPlayer();

        $this->assertTrue(Uuid::isValid((string) $this->player->getId()));
        $this->assertNull($this->player->getName());
        $this->assertEquals(HangmanPlayer::DEFAULT_LIVES, $this->player->getRemainingLives());
        $this->assertNull($this->player->getGame());

        $this->assertFalse($this->player->hasLost());
        $this->assertFalse($this->player->hasWon());
    }

    /**
     * @test
     */
    public function itShouldAcceptParameters()
    {
        $this->assertEquals($this->playerId, $this->player->getId());
        $this->assertEquals($this->name, $this->player->getName());
        $this->assertEquals($this->lives, $this->player->getRemainingLives());
        $this->assertEquals($this->game, $this->player->getGame());
        $this->assertEquals($this->externalReference, $this->player->getExternalReference());

        $this->assertFalse($this->player->hasLost());
        $this->assertFalse($this->player->hasWon());
    }

    /**
     * @test
     */
    public function itShouldNotLoseLifeWhenPlayingGoodLetter()
    {
        $this->player->playGoodLetter($this->firstLetter);
        $this->assertEquals($this->lives, $this->player->getRemainingLives());
    }

    /**
     * @test
     */
    public function itShouldLoseLifeWhenPlayingBadLetter()
    {
        $this->player->playBadLetter($this->firstLetter, 1);
        $this->assertEquals($this->lives-1, $this->player->getRemainingLives());
    }

    /**
     * @test
     */
    public function itShouldUpperPlayedLetters()
    {
        $this->player->playGoodLetter($this->firstLetter);
        $this->assertEquals([strtoupper($this->firstLetter)], $this->player->getPlayedLetters());

        $this->player->playBadLetter($this->secondLetter, 1);
        $this->assertEquals(
            [strtoupper($this->firstLetter), strtoupper($this->secondLetter)],
            $this->player->getPlayedLetters()
        );
    }

    /**
     * @test
     */
    public function itShouldReturnOnlyOnceEachPlayedLetter()
    {
        $this->player->playGoodLetter($this->firstLetter);
        $this->assertEquals([strtoupper($this->firstLetter)], $this->player->getPlayedLetters());

        $this->player->playGoodLetter($this->firstLetter);
        $this->assertEquals([strtoupper($this->firstLetter)], $this->player->getPlayedLetters());
    }

    /**
     * @test
     */
    public function itShouldWin()
    {
        $this->player->win('word');
        $this->assertTrue($this->player->hasWon());
    }

    /**
     * @test
     */
    public function itShouldLose()
    {
        $this->player->lose('word');
        $this->assertTrue($this->player->hasLost());
    }

    /**
     * @test
     */
    public function itShouldChangeNothingWhenHandlingHangmanBadLetterProposedEventForOtherPlayer()
    {
        $this->player->handleRecursively(
            new HangmanBadLetterProposedEvent(
                $this->gameId,
                $this->otherPlayerId,
                strtoupper($this->firstLetter),
                [],
                $this->livesLost,
                $this->lives - 1,
                ''
            )
        );

        $this->assertFalse($this->player->hasLost());
        $this->assertFalse($this->player->hasWon());
        $this->assertEquals($this->lives, $this->player->getRemainingLives());
        $this->assertEquals([], $this->player->getPlayedLetters());
    }

    /**
     * @test
     */
    public function itShouldChangeNothingWhenHandlingHangmanGoodLetterProposedEventForOtherPlayer()
    {
        $this->player->handleRecursively(
            new HangmanGoodLetterProposedEvent(
                $this->gameId,
                $this->otherPlayerId,
                strtoupper($this->firstLetter),
                [],
                $this->lives,
                ''
            )
        );

        $this->assertFalse($this->player->hasLost());
        $this->assertFalse($this->player->hasWon());
        $this->assertEquals($this->lives, $this->player->getRemainingLives());
        $this->assertEquals([], $this->player->getPlayedLetters());
    }

    /**
     * @test
     */
    public function itShouldChangeNothingWhenHandlingHangmanPlayerLostEventForOtherPlayer()
    {
        $this->player->handleRecursively(
            new HangmanPlayerLostEvent(
                $this->gameId,
                $this->otherPlayerId,
                [],
                0,
                '',
                ''
            )
        );

        $this->assertFalse($this->player->hasLost());
        $this->assertFalse($this->player->hasWon());
        $this->assertEquals($this->lives, $this->player->getRemainingLives());
        $this->assertEquals([], $this->player->getPlayedLetters());
    }

    /**
     * @test
     */
    public function itShouldChangeNothingWhenHandlingHangmanPlayerWinEventForOtherPlayer()
    {
        $this->player->handleRecursively(
            new HangmanPlayerWinEvent(
                $this->gameId,
                $this->otherPlayerId,
                [],
                $this->lives,
                ''
            )
        );

        $this->assertFalse($this->player->hasLost());
        $this->assertFalse($this->player->hasWon());
        $this->assertEquals($this->lives, $this->player->getRemainingLives());
        $this->assertEquals([], $this->player->getPlayedLetters());
    }
}
