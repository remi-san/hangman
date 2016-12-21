<?php
namespace Hangman\Test\Entity;

use Hangman\Entity\Hangman;
use Hangman\Entity\HangmanPlayer;
use Hangman\Event\HangmanBadLetterProposedEvent;
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

        $this->player = new HangmanPlayer(
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
    public function itShouldUpperPlayedLetters()
    {
        $this->player->playLetter($this->firstLetter);
        $this->assertEquals([strtoupper($this->firstLetter)], $this->player->getPlayedLetters());

        $this->player->playLetter($this->secondLetter);
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
        $this->player->playLetter($this->firstLetter);
        $this->assertEquals([strtoupper($this->firstLetter)], $this->player->getPlayedLetters());

        $this->player->playLetter($this->firstLetter);
        $this->assertEquals([strtoupper($this->firstLetter)], $this->player->getPlayedLetters());
    }

    /**
     * @test
     */
    public function itShouldLoseLife()
    {
        $this->player->loseLife();
        $this->assertEquals(--$this->lives, $this->player->getRemainingLives());
    }

    /**
     * @test
     */
    public function itShouldWin()
    {
        $this->player->win();
        $this->assertTrue($this->player->hasWon());
    }

    /**
     * @test
     */
    public function itShouldLose()
    {
        $this->player->lose();
        $this->assertTrue($this->player->hasLost());
    }

    /**
     * @test
     */
    public function itShouldChangeRemainingLivesWhenHandlingHangmanBadLetterProposedEventForPlayer()
    {
        $this->player->handleRecursively(
            $this->getBadLetterEvent($this->playerId)
        );

        $this->assertEquals($this->lives - $this->livesLost, $this->player->getRemainingLives());
    }

    /**
     * @test
     */
    public function itShouldChangeNothingWhenHandlingHangmanBadLetterProposedEventForOtherPlayer()
    {
        $this->player->handleRecursively(
            $this->getBadLetterEvent($this->otherPlayerId)
        );

        $this->assertEquals($this->lives, $this->player->getRemainingLives());
    }

    /**
     * @param PlayerId $playerId
     *
     * @return HangmanBadLetterProposedEvent
     */
    private function getBadLetterEvent(PlayerId $playerId)
    {
        return new HangmanBadLetterProposedEvent(
            $this->gameId,
            $playerId,
            strtoupper($this->firstLetter),
            [],
            $this->livesLost,
            $this->lives - 1,
            ''
        );
    }
}
