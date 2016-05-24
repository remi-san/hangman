<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanGameLostEvent;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanGameLostEventTest extends \PHPUnit_Framework_TestCase
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
        $word = 'word';

        $event = new HangmanGameLostEvent($id, $playerId, $word);

        $this->assertEquals($id, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());
        $this->assertEquals($word, $event->getWord());
        $this->assertEquals('Game lost', $event->getAsMessage());
    }
}
