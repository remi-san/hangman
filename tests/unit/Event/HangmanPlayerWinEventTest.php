<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanPlayerWinEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerWinEventTest extends \PHPUnit_Framework_TestCase
{
    /** @var MiniGameId */
    private $gameId;

    /** @var PlayerId */
    private $playerId;

    /** @var string[] */
    private $playedLetters;

    /** @var int */
    private $remainingLives;

    /** @var string */
    private $word;

    public function setUp()
    {
        $this->gameId = MiniGameId::create(666);
        $this->playerId = PlayerId::create(42);
        $this->playedLetters = ['A'];
        $this->remainingLives = 5;
        $this->word = 'ABC';
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldBuildPlayerWinEvent()
    {
        $event = new HangmanPlayerWinEvent(
            $this->gameId,
            $this->playerId,
            $this->playedLetters,
            $this->remainingLives,
            $this->word
        );

        $this->assertEquals($this->gameId, $event->getGameId());
        $this->assertEquals($this->playerId, $event->getPlayerId());
        $this->assertEquals($this->playedLetters, $event->getPlayedLetters());
        $this->assertEquals($this->remainingLives, $event->getRemainingLives());
        $this->assertEquals($this->word, $event->getWord());
    }
}
