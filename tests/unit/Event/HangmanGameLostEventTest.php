<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanGameLostEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanGameLostEventTest extends \PHPUnit_Framework_TestCase
{
    /** @var MiniGameId */
    private $gameId;

    /** @var PlayerId */
    private $playerId;

    /** @var string */
    private $word;

    public function setUp()
    {
        $this->gameId = MiniGameId::create(666);
        $this->playerId = PlayerId::create(42);
        $this->word = 'word';
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldBuildHangmanStartedEvent()
    {
        $event = new HangmanGameLostEvent($this->gameId, $this->playerId, $this->word);

        $this->assertEquals($this->gameId, $event->getGameId());
        $this->assertEquals($this->playerId, $event->getPlayerId());
        $this->assertEquals($this->word, $event->getWord());
    }
}
