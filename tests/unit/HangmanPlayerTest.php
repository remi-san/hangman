<?php
namespace Hangman\Test;

use Hangman\HangmanPlayer;
use MiniGame\Test\Mock\GameObjectMocker;
use MiniGameApp\Test\Mock\MiniGameAppMocker;
use MiniGameApp\Test\Mock\PlayerMock;
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
        $game = $this->getMiniGame($this->getMiniGameId(33));

        $player = new HangmanPlayer($id, $name, $game);

        $this->assertEquals($id, $player->getId());
        $this->assertEquals($name, $player->getName());
        $this->assertEquals($game, $player->getGame());
    }
}
