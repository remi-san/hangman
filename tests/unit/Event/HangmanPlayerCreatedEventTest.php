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

        $event = new HangmanPlayerCreatedEvent($gameId, $playerId, 'name', 6, 'ext');

        $this->assertEquals($gameId, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());
        $this->assertEquals('name', $event->getPlayerName());
        $this->assertEquals(6, $event->getLives());
        $this->assertEquals('ext', $event->getExternalReference());

        $this->assertEquals(
            array(
                'name' => 'hangman.player.created',
                'gameId' => 666,
                'playerId' => 42,
                'playerName' => 'name',
                'lives' => 6,
                'externalReference' => 'ext'
            ),
            $event->serialize()
        );

        $unserializedEvent = HangmanPlayerCreatedEvent::deserialize(
            array(
                'name' => 'hangman.player.created',
                'gameId' => 666,
                'playerId' => 42,
                'playerName' => 'name',
                'lives' => 6,
                'externalReference' => 'ext'
            )
        );

        $this->assertEquals(666, (string)$unserializedEvent->getGameId());
        $this->assertEquals(42, (string)$unserializedEvent->getPlayerId());
        $this->assertEquals('name', $unserializedEvent->getPlayerName());
        $this->assertEquals(6, $unserializedEvent->getLives());
        $this->assertEquals('ext', $unserializedEvent->getExternalReference());
    }
}
