<?php
namespace Hangman\Test;

use Hangman\Entity\HangmanPlayer;
use MiniGame\Test\Mock\GameObjectMocker;
use Rhumsaa\Uuid\Uuid;

class HangmanPlayerTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

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
        $game = $this->getMiniGame($this->getMiniGameId(33));

        $player = new HangmanPlayer(null, $name);

        $this->assertTrue(Uuid::isValid($player->getId()->getId()));
        $this->assertEquals($name, $player->getName());
        $this->assertEquals(6, $player->getRemainingLives());
        $this->assertNull($player->getGame());

        $player->setGame($game);

        $this->assertEquals($game, $player->getGame());
    }

    /**
     * @test
     */
    public function testGetters()
    {
        $id = $this->getPlayerId(42);
        $name = 'Douglas';
        $lives = 5;
        $game = $this->getMiniGame($this->getMiniGameId(33));

        $player = new HangmanPlayer($id, $name, $lives, $game);

        $this->assertEquals($id, $player->getId());
        $this->assertEquals($name, $player->getName());
        $this->assertEquals($lives, $player->getRemainingLives());
        $this->assertEquals($game, $player->getGame());
    }

    /**
     * @test
     */
    public function testDomainMethods()
    {
        $id = $this->getPlayerId(42);
        $name = 'Douglas';
        $lives = 5;
        $game = $this->getMiniGame($this->getMiniGameId(33));

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
}
