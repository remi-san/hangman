<?php
namespace Hangman\Test;

use Hangman\Event\HangmanGameCreatedEvent;
use Hangman\Event\HangmanPlayerCreatedEvent;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanEventTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @test
     */
    public function testPlayerCreated()
    {
        $id = $this->getMiniGameId(666);
        $player = $this->getPlayer();

        $event = new HangmanPlayerCreatedEvent($id, $player);

        $this->assertEquals($id, $event->getGameId());
        $this->assertEquals($player, $event->getPlayer());
    }
}
