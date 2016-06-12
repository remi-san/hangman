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
    }
}
