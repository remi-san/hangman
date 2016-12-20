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
        $id = PlayerId::create(42);
        $name = 'Douglas';
        $lives = 5;
        $game = Hangman::createGame(MiniGameId::create(33), 'word');

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
        $id = PlayerId::create(42);
        $name = 'Douglas';
        $lives = 5;
        $game = Hangman::createGame(MiniGameId::create(33), 'word');

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
        $id = PlayerId::create(42);
        $name = 'Douglas';
        $lives = 5;
        $game = Hangman::createGame(MiniGameId::create(33), 'word');

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
        $id = PlayerId::create(42);
        $name = 'Douglas';
        $lives = 5;
        $game = Hangman::createGame(MiniGameId::create(33), 'word');

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
        $id = PlayerId::create(42);
        $name = 'Douglas';
        $lives = 5;
        $game = Hangman::createGame(MiniGameId::create(33), 'word');

        $player = new HangmanPlayer($id, $name, $lives, $game);

        $player->handleRecursively(
            new HangmanBadLetterProposedEvent(
                MiniGameId::create(33),
                PlayerId::create(25),
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
        $id = PlayerId::create(42);
        $name = 'Douglas';
        $lives = 5;
        $game = Hangman::createGame(MiniGameId::create(33), 'word');

        $player = new HangmanPlayer($id, $name, $lives, $game);

        $player->handleRecursively(
            new HangmanBadLetterProposedEvent(
                MiniGameId::create(33),
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
