<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanPlayerProposedInvalidAnswerEvent;
use Hangman\Test\Mock\HangmanMocker;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanPlayerProposedInvalidAnswerEventTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker, HangmanMocker;

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
        $answer = $this->getAnswer('answer');

        $event = new HangmanPlayerProposedInvalidAnswerEvent(
            $gameId,
            $playerId,
            $answer
        );

        $this->assertEquals($gameId, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());
        $this->assertEquals($answer, $event->getAnswer());
        $this->assertEquals('Invalid answer', $event->getAsMessage());
    }
}
