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

    /**
     * @test
     */
    public function testSerialize()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);
        $answer = $this->getAnswer('answer');

        $event = new HangmanPlayerProposedInvalidAnswerEvent(
            $gameId,
            $playerId,
            $answer
        );

        $this->assertEquals(
            array(
                'name' => 'hangman.player.invalid-answer',
                'gameId' => $gameId->getId(),
                'playerId' => $playerId->getId(),
                'answer' => $answer->getText()
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
        $answer = 'answer';

        $unserializedEvent = HangmanPlayerProposedInvalidAnswerEvent::deserialize(
            array(
                'name' => 'hangman.player.invalid-answer',
                'gameId' => $gameId,
                'playerId' => $playerId,
                'answer' => $answer
            )
        );

        $this->assertEquals($gameId, $unserializedEvent->getGameId()->getId());
        $this->assertEquals($playerId, $unserializedEvent->getPlayerId()->getId());
        $this->assertEquals($answer, $unserializedEvent->getAnswer()->getText());
    }
}
