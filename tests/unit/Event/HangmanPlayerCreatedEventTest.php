<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanPlayerCreatedEvent;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanPlayerCreatedEventTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testPlayerCreated()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);
        $lives = 6;

        $event = new HangmanPlayerCreatedEvent($gameId, $playerId, 'name', $lives, 'ext');

        $this->assertEquals($gameId, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());
        $this->assertEquals('name', $event->getPlayerName());
        $this->assertEquals($lives, $event->getLives());
        $this->assertEquals('ext', $event->getExternalReference());
    }
}
