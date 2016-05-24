<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanPlayerLostEvent;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanPlayerLostEventTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testPlayerLost()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);
        $playedLetters = array('A');
        $remainingLives = 5;
        $wordSoFar = 'A _ _';
        $word = 'ABC';

        $event = new HangmanPlayerLostEvent(
            $gameId,
            $playerId,
            $playedLetters,
            $remainingLives,
            $wordSoFar,
            $word
        );

        $this->assertEquals($gameId, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());
        $this->assertEquals($playedLetters, $event->getPlayedLetters());
        $this->assertEquals($remainingLives, $event->getRemainingLives());
        $this->assertEquals($wordSoFar, $event->getWordFound());
        $this->assertEquals($word, $event->getWord());
        $this->assertEquals(sprintf('You lose... The word was %s.', $event->getWord()), $event->getAsMessage());
    }
}
