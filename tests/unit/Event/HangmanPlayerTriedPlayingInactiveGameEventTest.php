<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanPlayerTriedPlayingInactiveGameEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerTriedPlayingInactiveGameEventTest extends \PHPUnit_Framework_TestCase
{
    /** @var MiniGameId */
    private $gameId;

    /** @var PlayerId */
    private $playerId;

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
    public function itShouldBuildHangmanPlayerTriedPlayingInactiveGameEvent()
    {
        $event = new HangmanPlayerTriedPlayingInactiveGameEvent(
            $this->gameId,
            $this->playerId
        );

        $this->assertEquals($this->gameId, $event->getGameId());
        $this->assertEquals($this->playerId, $event->getPlayerId());
    }
}
