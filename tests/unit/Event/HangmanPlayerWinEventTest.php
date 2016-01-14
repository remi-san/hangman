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

    /**
     * @test
     */
    public function testSerialize()
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

        $this->assertEquals(
            array(
                'name' => 'hangman.player.win',
                'gameId' => (string) $gameId,
                'playerId' => (string) $playerId,
                'playedLetters' => $playedLetters,
                'remainingLives' => $remainingLives,
                'word' => $word
            ),
            $event->serialize()
        );
    }

    /**
     * @test
     */
    public function testUnserialize()
    {
        $gameId = 666;
        $playerId = 42;
        $playedLetters = array('A');
        $remainingLives = 5;
        $word = 'ABC';

        $unserializedEvent = HangmanPlayerWinEvent::deserialize(
            array(
                'name' => 'hangman.player.win',
                'gameId' => $gameId,
                'playerId' => $playerId,
                'playedLetters' => $playedLetters,
                'remainingLives' => $remainingLives,
                'word' => $word
            )
        );

        $this->assertEquals($gameId, (string) $unserializedEvent->getGameId());
        $this->assertEquals($playerId, (string) $unserializedEvent->getPlayerId());
        $this->assertEquals($playedLetters, $unserializedEvent->getPlayedLetters());
        $this->assertEquals($remainingLives, $unserializedEvent->getRemainingLives());
        $this->assertEquals($word, $unserializedEvent->getWord());
    }
}
