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
        $this->assertEquals('Game started', $event->getAsMessage());
    }
}
