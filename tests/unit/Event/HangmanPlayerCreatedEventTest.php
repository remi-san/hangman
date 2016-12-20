<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanPlayerCreatedEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerCreatedEventTest extends \PHPUnit_Framework_TestCase
{
    /** @var MiniGameId */
    private $gameId;

    /** @var PlayerId */
    private $playerId;

    /** @var int */
    private $lives;

    /** @var string */
    private $name;

    /** @var string */
    private $externalReference;

    public function setUp()
    {
        $this->gameId = MiniGameId::create(666);
        $this->playerId = PlayerId::create(42);
        $this->lives = 6;
        $this->name = 'name';
        $this->externalReference = 'extReference';
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldBuildPlayerCreatedEvent()
    {
        $event = new HangmanPlayerCreatedEvent(
            $this->gameId,
            $this->playerId,
            $this->name,
            $this->lives,
            $this->externalReference
        );

        $this->assertEquals($this->gameId, $event->getGameId());
        $this->assertEquals($this->playerId, $event->getPlayerId());
        $this->assertEquals($this->name, $event->getPlayerName());
        $this->assertEquals($this->lives, $event->getLives());
        $this->assertEquals($this->externalReference, $event->getExternalReference());
    }
}
