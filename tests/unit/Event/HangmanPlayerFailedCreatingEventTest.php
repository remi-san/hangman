<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanPlayerFailedCreatingEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerFailedCreatingEventTest extends \PHPUnit_Framework_TestCase
{
    /** @var MiniGameId */
    private $gameId;

    /** @var PlayerId */
    private $playerId;

    /** @var string */
    private $externalReference;

    public function setUp()
    {
        $this->gameId = MiniGameId::create(666);
        $this->playerId = PlayerId::create(42);
        $this->externalReference = 'extReference';
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldBuildHangmanPlayerFailedCreatingEvent()
    {
        $event = new HangmanPlayerFailedCreatingEvent(
            $this->gameId,
            $this->playerId,
            $this->externalReference
        );

        $this->assertEquals($this->gameId, $event->getGameId());
        $this->assertEquals($this->playerId, $event->getPlayerId());
        $this->assertEquals($this->externalReference, $event->getExternalReference());
    }
}
