<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanPlayerTurnEvent;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanPlayerTurnEventTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testPlayerTurn()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);

        $event = new HangmanPlayerTurnEvent(
            $gameId,
            $playerId
        );

        $this->assertEquals($gameId, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());
        $this->assertEquals('It is your turn to play', $event->getAsMessage());
    }

    /**
     * @test
     */
    public function testSerialize()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);

        $event = new HangmanPlayerTurnEvent(
            $gameId,
            $playerId
        );

        $this->assertEquals(
            array(
                'name' => 'hangman.player.turn',
                'gameId' => (string) $gameId,
                'playerId' => (string) $playerId
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

        $unserializedEvent = HangmanPlayerTurnEvent::deserialize(
            array(
                'name' => 'hangman.player.turn',
                'gameId' => (string) $gameId,
                'playerId' => (string) $playerId
            )
        );

        $this->assertEquals($gameId, (string) $unserializedEvent->getGameId());
        $this->assertEquals($playerId, (string) $unserializedEvent->getPlayerId());
    }
}
