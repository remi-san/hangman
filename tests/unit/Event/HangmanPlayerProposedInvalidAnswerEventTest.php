<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanPlayerProposedInvalidAnswerEvent;
use Hangman\Move\Answer;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerProposedInvalidAnswerEventTest extends \PHPUnit_Framework_TestCase
{
    /** @var MiniGameId */
    private $gameId;

    /** @var PlayerId */
    private $playerId;

    /** @var Answer */
    private $answer;

    public function setUp()
    {
        $this->gameId = MiniGameId::create(666);
        $this->playerId = PlayerId::create(42);
        $this->answer = \Mockery::mock(Answer::class);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldBuildHangmanPlayerProposedInvalidAnswerEvent()
    {
        $event = new HangmanPlayerProposedInvalidAnswerEvent(
            $this->gameId,
            $this->playerId,
            $this->answer
        );

        $this->assertEquals($this->gameId, $event->getGameId());
        $this->assertEquals($this->playerId, $event->getPlayerId());
        $this->assertEquals($this->answer, $event->getAnswer());
    }
}
