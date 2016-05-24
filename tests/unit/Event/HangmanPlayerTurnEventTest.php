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
}
