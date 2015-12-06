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
    }
}
