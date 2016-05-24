<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanPlayerWinEvent;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanPlayerWinEventTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testPlayerWin()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);
        $playedLetters = array('A');
        $remainingLives = 5;
        $word = 'ABC';

        $event = new HangmanPlayerWinEvent(
            $gameId,
            $playerId,
            $playedLetters,
            $remainingLives,
            $word
        );

        $this->assertEquals($gameId, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());
        $this->assertEquals($playedLetters, $event->getPlayedLetters());
        $this->assertEquals($remainingLives, $event->getRemainingLives());
        $this->assertEquals($word, $event->getWord());
        $this->assertEquals(sprintf('Congratulations! The word was %s.', $event->getWord()), $event->getAsMessage());
    }
}
