<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanPlayerTriedPlayingInactiveGameEvent;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanPlayerTriedPlayingInactiveGameEventTest extends \PHPUnit_Framework_TestCase
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

        $event = new HangmanPlayerTriedPlayingInactiveGameEvent(
            $gameId,
            $playerId
        );

        $this->assertEquals($gameId, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());
        $this->assertEquals('You cannot play.', $event->getAsMessage());
    }

    /**
     * @test
     */
    public function testSerialize()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);

        $event = new HangmanPlayerTriedPlayingInactiveGameEvent(
            $gameId,
            $playerId
        );

        $this->assertEquals(
            array(
                'name' => 'hangman.player.inactive-game',
                'gameId' => $gameId->getId(),
                'playerId' => $playerId->getId()
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

        $unserializedEvent = HangmanPlayerTriedPlayingInactiveGameEvent::deserialize(
            array(
                'name' => 'hangman.player.inactive-game',
                'gameId' => $gameId,
                'playerId' => $playerId
            )
        );

        $this->assertEquals($gameId, $unserializedEvent->getGameId()->getId());
        $this->assertEquals($playerId, $unserializedEvent->getPlayerId()->getId());
    }
}
