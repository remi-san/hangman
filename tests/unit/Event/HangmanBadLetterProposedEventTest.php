<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanBadLetterProposedEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanBadLetterProposedEventTest extends \PHPUnit_Framework_TestCase
{
    /** @var MiniGameId */
    private $gameId;

    /** @var PlayerId */
    private $playerId;

    /** @var string */
    private $letter;

    /** @var string[] */
    private $playedLetters;

    /** @var int */
    private $livesLost;

    /** @var int */
    private $remainingLives;

    /** @var string */
    private $wordSoFar;

    public function setUp()
    {
        $this->gameId = MiniGameId::create(666);
        $this->playerId = PlayerId::create(42);
        $this->letter = 'A';
        $this->playedLetters = ['A'];
        $this->livesLost = 1;
        $this->remainingLives = 5;
        $this->wordSoFar = 'A _ _';
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldBuildBadLetterProposedEvent()
    {
        $event = new HangmanBadLetterProposedEvent(
            $this->gameId,
            $this->playerId,
            $this->letter,
            $this->playedLetters,
            $this->livesLost,
            $this->remainingLives,
            $this->wordSoFar
        );

        $this->assertEquals($this->gameId, $event->getGameId());
        $this->assertEquals($this->playerId, $event->getPlayerId());
        $this->assertEquals($this->letter, $event->getLetter());
        $this->assertEquals($this->playedLetters, $event->getPlayedLetters());
        $this->assertEquals($this->livesLost, $event->getLivesLost());
        $this->assertEquals($this->remainingLives, $event->getRemainingLives());
        $this->assertEquals($this->wordSoFar, $event->getWordSoFar());
        $this->assertEquals($this->wordSoFar, $event->getFeedback());
    }
}
