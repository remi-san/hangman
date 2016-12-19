<?php
namespace Hangman\Test\Exception;

use Hangman\Exception\HangmanPlayerOptionsException;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanExceptionTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    private $gameId;

    private $playerId;

    public function setUp()
    {
        $this->gameId = $this->getMiniGameId(666);

        $this->playerId = $this->getPlayerId(42);
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testHangmanOptionsWithWord()
    {
        $event = new HangmanPlayerOptionsException($this->playerId, $this->gameId);

        $this->assertEquals($this->playerId, $event->getPlayerId());
        $this->assertEquals($this->gameId, $event->getMiniGameId());
    }
}
