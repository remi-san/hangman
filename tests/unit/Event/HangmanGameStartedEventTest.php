<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanGameStartedEvent;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanGameStartedEventTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testHangmanStarted()
    {
        $id = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);

        $event = new HangmanGameStartedEvent($id, $playerId);

        $this->assertEquals($id, $event->getGameId());

        $this->assertEquals(
            array(
                'name' => 'hangman.started',
                'gameId' => 666,
                'playerId' => 42
            ),
            $event->serialize()
        );

        $unserializedEvent = HangmanGameStartedEvent::deserialize(
            array(
                'name' => 'hangman.started',
                'gameId' => 666,
                'playerId' => 42
            )
        );

        $this->assertEquals(666, (string)$unserializedEvent->getGameId());
        $this->assertEquals(42, (string)$unserializedEvent->getPlayerId());
    }
}
