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

        $this->assertEquals(
            array(
                'name' => 'hangman.player.deleted',
                'gameId' => 666,
                'playerId' => 42
            ),
            $event->serialize()
        );

        $unserializedEvent = HangmanPlayerDeletedEvent::deserialize(
            array(
                'name' => 'hangman.player.deleted',
                'gameId' => 666,
                'playerId' => 42
            )
        );

        $this->assertEquals(666, (string)$unserializedEvent->getGameId());
        $this->assertEquals(42, (string)$unserializedEvent->getPlayerId());
    }
}
