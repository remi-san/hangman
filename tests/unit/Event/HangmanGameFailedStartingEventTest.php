<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanGameFailedStartingEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanGameFailedStartingEventTest extends \PHPUnit_Framework_TestCase
{
    /** @var MiniGameId */
    private $gameId;

    /** @var PlayerId */
    private $playerId;

    /** @var string */
    private $reason;

    public function setUp()
    {
        $this->gameId = MiniGameId::create(666);
        $this->playerId = PlayerId::create(42);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldBuildHangmanGameFailedStartingEventWithNoPlayer()
    {
        $this->givenThereWasNoPlayer();

        $event = new HangmanGameFailedStartingEvent(
            $this->gameId,
            $this->playerId,
            $this->reason
        );

        $this->assertEquals($this->gameId, $event->getGameId());
        $this->assertEquals($this->playerId, $event->getPlayerId());
        $this->assertEquals($this->reason, $event->getReason());
    }

    /**
     * @test
     */
    public function itShouldBuildHangmanGameFailedStartingEventWithBadState()
    {
        $this->givenABadState();

        $event = new HangmanGameFailedStartingEvent(
            $this->gameId,
            $this->playerId,
            $this->reason
        );

        $this->assertEquals($this->gameId, $event->getGameId());
        $this->assertEquals($this->playerId, $event->getPlayerId());
        $this->assertEquals($this->reason, $event->getReason());
    }

    /**
     * @test
     */
    public function itShouldBuildHangmanGameFailedStartingEventWithUnknownReason()
    {
        $this->givenAnUnknownReason();

        $event = new HangmanGameFailedStartingEvent(
            $this->gameId,
            $this->playerId,
            $this->reason
        );

        $this->assertEquals($this->gameId, $event->getGameId());
        $this->assertEquals($this->playerId, $event->getPlayerId());
        $this->assertEquals($this->reason, $event->getReason());
    }

    private function givenThereWasNoPlayer()
    {
        $this->reason = HangmanGameFailedStartingEvent::NO_PLAYER;
    }

    private function givenABadState()
    {
        $this->reason = HangmanGameFailedStartingEvent::BAD_STATE;
    }

    private function givenAnUnknownReason()
    {
        $this->reason = 'unknown';
    }
}
