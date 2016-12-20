<?php
namespace Hangman\Test\Exception;

use Hangman\Exception\HangmanPlayerOptionsException;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerOptionsExceptionTest extends \PHPUnit_Framework_TestCase
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
    public function itShouldBuildHangmanOptionsWithWordException()
    {
        $event = new HangmanPlayerOptionsException($this->playerId, $this->gameId);

        $this->assertEquals($this->playerId, $event->getPlayerId());
        $this->assertEquals($this->gameId, $event->getMiniGameId());
    }
}
