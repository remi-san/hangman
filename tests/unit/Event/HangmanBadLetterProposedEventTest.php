<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanBadLetterProposedEvent;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanBadLetterProposedEventTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testBadLetterProposed()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);
        $letter = 'A';
        $playedLetters = array('A');
        $livesLost = 1;
        $remainingLives = 5;
        $wordSoFar = 'A _ _';

        $event = new HangmanBadLetterProposedEvent(
            $gameId,
            $playerId,
            $letter,
            $playedLetters,
            $livesLost,
            $remainingLives,
            $wordSoFar
        );

        $this->assertEquals($gameId, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());
        $this->assertEquals($letter, $event->getLetter());
        $this->assertEquals($playedLetters, $event->getPlayedLetters());
        $this->assertEquals($livesLost, $event->getLivesLost());
        $this->assertEquals($remainingLives, $event->getRemainingLives());
        $this->assertEquals($wordSoFar, $event->getWordSoFar());
        $this->assertEquals($wordSoFar, $event->getFeedback());
    }
}
