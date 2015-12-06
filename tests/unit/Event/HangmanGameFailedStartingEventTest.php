<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanGameFailedStartingEvent;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanGameFailedStartingEventTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testNoPlayer()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);
        $reason = HangmanGameFailedStartingEvent::NO_PLAYER;

        $event = new HangmanGameFailedStartingEvent(
            $gameId,
            $playerId,
            $reason
        );

        $this->assertEquals($gameId, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());
        $this->assertEquals(
            "You can't start a game that has no player.",
            $event->getAsMessage()
        );
    }

    /**
     * @test
     */
    public function testBadState()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);
        $reason = HangmanGameFailedStartingEvent::BAD_STATE;

        $event = new HangmanGameFailedStartingEvent(
            $gameId,
            $playerId,
            $reason
        );

        $this->assertEquals($gameId, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());
        $this->assertEquals(
            "You can't start a game that's already started or is over.",
            $event->getAsMessage()
        );
    }

    /**
     * @test
     */
    public function testUnknown()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);
        $reason = 'unknown';

        $event = new HangmanGameFailedStartingEvent(
            $gameId,
            $playerId,
            $reason
        );

        $this->assertEquals($gameId, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());
        $this->assertEquals(
            "Game failed starting for unknown reasons",
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
        $reason = HangmanGameFailedStartingEvent::BAD_STATE;

        $event = new HangmanGameFailedStartingEvent(
            $gameId,
            $playerId,
            $reason
        );

        $this->assertEquals(
            array(
                'name' => HangmanGameFailedStartingEvent::NAME,
                'gameId' => $gameId->getId(),
                'playerId' => $playerId->getId(),
                'reason' => $reason
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
        $reason = HangmanGameFailedStartingEvent::BAD_STATE;

        $event = HangmanGameFailedStartingEvent::deserialize(
            array(
                'name' => HangmanGameFailedStartingEvent::NAME,
                'gameId' => $gameId,
                'playerId' => $playerId,
                'reason' => $reason
            )
        );

        $this->assertEquals($gameId, $event->getGameId()->getId());
        $this->assertEquals($playerId, $event->getPlayerId()->getId());
        $this->assertEquals(
            "You can't start a game that's already started or is over.",
            $event->getAsMessage()
        );
    }
}
