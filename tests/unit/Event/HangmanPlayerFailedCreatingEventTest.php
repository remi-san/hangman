<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanPlayerFailedCreatingEvent;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanPlayerFailedCreatingEventTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function test()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);
        $extRef = 'ext-ref';

        $event = new HangmanPlayerFailedCreatingEvent(
            $gameId,
            $playerId,
            $extRef
        );

        $this->assertEquals($gameId, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());
        $this->assertEquals($extRef, $event->getExternalReference());
        $this->assertEquals(
            'You cannot add a player to a game that has already started.',
            $event->getAsMessage()
        );
    }

    /**
     * @test
     */
    public function testSerialize()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);
        $extRef = 'ext-ref';

        $event = new HangmanPlayerFailedCreatingEvent(
            $gameId,
            $playerId,
            $extRef
        );

        $this->assertEquals(
            array(
                'name' => 'hangman.player.failed-creating',
                'gameId' => $gameId->getId(),
                'playerId' => $playerId->getId(),
                'externalReference' => $extRef
            ),
            $event->serialize()
        );
    }

    /**
     * @test
     */
    public function testDeserialize()
    {
        $gameId = 666;
        $playerId = 42;
        $extRef = 'ext-ref';

        $unserializedEvent = HangmanPlayerFailedCreatingEvent::deserialize(
            array(
                'name' => 'hangman.player.failed-creating',
                'gameId' => $gameId,
                'playerId' => $playerId,
                'externalReference' => $extRef
            )
        );

        $this->assertEquals($gameId, $unserializedEvent->getGameId()->getId());
        $this->assertEquals($playerId, $unserializedEvent->getPlayerId()->getId());
        $this->assertEquals($extRef, $unserializedEvent->getExternalReference());
    }
}
