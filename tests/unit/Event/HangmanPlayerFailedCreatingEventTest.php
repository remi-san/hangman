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
}
