<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanGameCreatedEvent;
use MiniGame\Entity\MiniGameId;

class HangmanGameCreatedEventEventTest extends \PHPUnit_Framework_TestCase
{
    /** @var MiniGameId */
    private $id;

    /** @var string */
    private $word;

    public function setUp()
    {
        $this->id = MiniGameId::create(666);
        $this->word = 'TEST';
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldBuildHangmanCreatedEvent()
    {
        $event = new HangmanGameCreatedEvent($this->id, $this->word);

        $this->assertEquals($this->id, $event->getGameId());
        $this->assertEquals($this->word, $event->getWord());
    }
}
