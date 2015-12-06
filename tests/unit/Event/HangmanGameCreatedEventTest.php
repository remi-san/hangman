<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanGameCreatedEvent;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanGameCreatedEventEventTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testHangmanCreated()
    {
        $id = $this->getMiniGameId(666);
        $word = 'TEST';

        $event = new HangmanGameCreatedEvent($id, $word);

        $this->assertEquals($id, $event->getGameId());
        $this->assertEquals($word, $event->getWord());
        $this->assertEquals('Game created', $event->getAsMessage());
    }

    /**
     * @test
     */
    public function testSerialize()
    {
        $id = $this->getMiniGameId(666);
        $word = 'TEST';

        $event = new HangmanGameCreatedEvent($id, $word);

        $this->assertEquals(
            array(
                'name' => 'hangman.created',
                'gameId' => $id->getId(),
                'word' => $word
            ),
            $event->serialize()
        );
    }

    /**
     * @test
     */
    public function testDeserialize()
    {
        $id = 666;
        $word = 'TEST';

        $unserializedEvent = HangmanGameCreatedEvent::deserialize(
            array(
                'name' => 'hangman.created',
                'gameId' => $id,
                'word' => $word
            )
        );

        $this->assertEquals($id, $unserializedEvent->getGameId()->getId());
        $this->assertEquals($word, $unserializedEvent->getWord());
    }
}
