<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanPlayerLostEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerLostEventTest extends \PHPUnit_Framework_TestCase
{
    /** @var MiniGameId */
    private $gameId;

    /** @var PlayerId */
    private $playerId;

    /** @var string[] */
    private $playedLetters;

    /** @var int */
    private $livesLost;

    /** @var int */
    private $remainingLives;

    /** @var string */
    private $wordSoFar;

    /** @var string */
    private $word;

    public function setUp()
    {
        $this->gameId = MiniGameId::create(666);
        $this->playerId = PlayerId::create(42);
        $this->playedLetters = ['A'];
        $this->livesLost = 1;
        $this->remainingLives = 5;
        $this->wordSoFar = 'A _ _';
        $this->word = 'ABC';
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldBuildPlayerLostEvent()
    {
        $event = new HangmanPlayerLostEvent(
            $this->gameId,
            $this->playerId,
            $this->playedLetters,
            $this->remainingLives,
            $this->wordSoFar,
            $this->word
        );

        $this->assertEquals($this->gameId, $event->getGameId());
        $this->assertEquals($this->playerId, $event->getPlayerId());
        $this->assertEquals($this->playedLetters, $event->getPlayedLetters());
        $this->assertEquals($this->remainingLives, $event->getRemainingLives());
        $this->assertEquals($this->wordSoFar, $event->getWordFound());
        $this->assertEquals($this->word, $event->getWord());
    }
}
