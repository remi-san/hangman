<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanPlayerDeletedEvent;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanPlayerDeletedEventTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testPlayerDeleted()
    {
        $id = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);

        $event = new HangmanPlayerDeletedEvent($id, $playerId);

        $this->assertEquals($id, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());
        $this->assertEquals('Player deleted', $event->getAsMessage());
    }

    /**
     * @test
     */
    public function testSerialize()
    {
        $id = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);

        $event = new HangmanPlayerDeletedEvent($id, $playerId);

        $this->assertEquals(
            array(
                'name' => 'hangman.player.deleted',
                'gameId' => $id->getId(),
                'playerId' => $playerId->getId()
            ),
            $event->serialize()
        );
    }

    /**
     * @test
     */
    public function testUnserialize()
    {
        $id = 666;
        $playerId = 42;

        $unserializedEvent = HangmanPlayerDeletedEvent::deserialize(
            array(
                'name' => 'hangman.player.deleted',
                'gameId' => $id,
                'playerId' => $playerId
            )
        );

        $this->assertEquals($id, $unserializedEvent->getGameId()->getId());
        $this->assertEquals($playerId, $unserializedEvent->getPlayerId()->getId());
    }
}
