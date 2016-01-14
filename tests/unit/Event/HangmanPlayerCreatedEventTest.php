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
        $this->assertEquals('Player created', $event->getAsMessage());
    }

    /**
     * @test
     */
    public function testSerialize()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);
        $lives = 6;

        $event = new HangmanPlayerCreatedEvent($gameId, $playerId, 'name', $lives, 'ext');

        $this->assertEquals(
            array(
                'name' => 'hangman.player.created',
                'gameId' => (string) $gameId,
                'playerId' => (string) $playerId,
                'playerName' => 'name',
                'lives' => $lives,
                'externalReference' => 'ext'
            ),
            $event->serialize()
        );
    }

    /**
     * @test
     */
    public function testUnserialize()
    {
        $gameId = 666;
        $playerId = 42;
        $lives = 6;

        $unserializedEvent = HangmanPlayerCreatedEvent::deserialize(
            array(
                'name' => 'hangman.player.created',
                'gameId' => $gameId,
                'playerId' => $playerId,
                'playerName' => 'name',
                'lives' => $lives,
                'externalReference' => 'ext'
            )
        );

        $this->assertEquals($gameId, (string) $unserializedEvent->getGameId());
        $this->assertEquals($playerId, (string) $unserializedEvent->getPlayerId());
        $this->assertEquals('name', $unserializedEvent->getPlayerName());
        $this->assertEquals($lives, $unserializedEvent->getLives());
        $this->assertEquals('ext', $unserializedEvent->getExternalReference());
    }
}
