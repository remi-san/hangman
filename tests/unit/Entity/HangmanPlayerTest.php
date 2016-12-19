<?php
namespace Hangman\Test\Entity;

use Hangman\Entity\HangmanPlayer;
use Hangman\Event\HangmanBadLetterProposedEvent;
use Hangman\Test\Mock\HangmanMocker;
use MiniGame\Test\Mock\GameObjectMocker;
use Rhumsaa\Uuid\Uuid;

class HangmanPlayerTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker, HangmanMocker;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testId()
    {
        $name = 'Douglas';

        $player = new HangmanPlayer(null, $name);

        $this->assertTrue(Uuid::isValid((string) $player->getId()));
        $this->assertEquals($name, $player->getName());
        $this->assertEquals(6, $player->getRemainingLives());
        $this->assertNull($player->getGame());
    }

    /**
     * @test
     */
    public function testGetters()
    {
        $id = $this->getPlayerId(42);
        $name = 'Douglas';
        $lives = 5;
        $game = $this->getHangmanMiniGame($this->getMiniGameId(33));

        $player = new HangmanPlayer($id, $name, $lives, $game, 'ext');

        $this->assertEquals($id, $player->getId());
        $this->assertEquals($name, $player->getName());
        $this->assertEquals($lives, $player->getRemainingLives());
        $this->assertEquals($game, $player->getGame());
        $this->assertEquals('ext', $player->getExternalReference());
    }

    /**
     * @test
     */
    public function testDomainMethods()
    {
        $id = $this->getPlayerId(42);
        $name = 'Douglas';
        $lives = 5;
        $game = \Mockery::mock('\Hangman\Entity\Hangman');
        $game->shouldReceive('getId')->andReturn($this->getMiniGameId(33));

        $a = 'a';
        $b = 'b';

        $player = new HangmanPlayer($id, $name, $lives, $game);

        $player->loseLife();
        $this->assertEquals(--$lives, $player->getRemainingLives());

        $player->playLetter($a);
        $this->assertEquals(array ('A'=>'A'), $player->getPlayedLetters());

        $player->playLetter($a);
        $this->assertEquals(array ('A'=>'A'), $player->getPlayedLetters());

        $player->playLetter($b);
        $this->assertEquals(array ('A'=>'A', 'B'=>'B'), $player->getPlayedLetters());
    }

    /**
     * @test
     */
    public function testWin()
    {
        $id = $this->getPlayerId(42);
        $name = 'Douglas';
        $lives = 5;
        $game = \Mockery::mock('\Hangman\Entity\Hangman');
        $game->shouldReceive('getId')->andReturn($this->getMiniGameId(33));

        $player = new HangmanPlayer($id, $name, $lives, $game);

        $this->assertFalse($player->hasLost());
        $this->assertFalse($player->hasWon());

        $player->win();
        $this->assertTrue($player->hasWon());
    }

    /**
     * @test
     */
    public function testLose()
    {
        $id = $this->getPlayerId(42);
        $name = 'Douglas';
        $lives = 5;
        $game = \Mockery::mock('\Hangman\Entity\Hangman');
        $game->shouldReceive('getId')->andReturn($this->getMiniGameId(33));

        $player = new HangmanPlayer($id, $name, $lives, $game);

        $this->assertFalse($player->hasLost());
        $this->assertFalse($player->hasWon());

        $player->lose();
        $this->assertTrue($player->hasLost());
    }

    /**
     * @test
     */
    public function testHandleHangmanBadLetterProposedEventForOtherPlayer()
    {
        $id = $this->getPlayerId(42);
        $name = 'Douglas';
        $lives = 5;
        $game = \Mockery::mock('\Hangman\Entity\Hangman');

        $player = new HangmanPlayer($id, $name, $lives, $game);

        $player->handleRecursively(
            new HangmanBadLetterProposedEvent(
                $this->getMiniGameId(33),
                $this->getPlayerId(25),
                'A',
                array(),
                1,
                $lives-1,
                ''
            )
        );

        $this->assertEquals($lives, $player->getRemainingLives());
    }

    /**
     * @test
     */
    public function testHandleHangmanBadLetterProposedEventForPlayer()
    {
        $id = $this->getPlayerId(42);
        $name = 'Douglas';
        $lives = 5;
        $game = \Mockery::mock('\Hangman\Entity\Hangman');

        $player = new HangmanPlayer($id, $name, $lives, $game);

        $player->handleRecursively(
            new HangmanBadLetterProposedEvent(
                $this->getMiniGameId(33),
                $id,
                'A',
                array(),
                1,
                $lives-1,
                ''
            )
        );

        $this->assertEquals($lives-1, $player->getRemainingLives());
    }
}