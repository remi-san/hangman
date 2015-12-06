<?php
namespace Hangman\Test\Event;

use Hangman\Event\HangmanGoodLetterProposedEvent;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanGoodLetterProposedEventTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testGoodLetterProposed()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);
        $letter = 'A';
        $playedLetters = array('A');
        $remainingLives = 5;
        $wordSoFar = 'A _ _';

        $event = new HangmanGoodLetterProposedEvent(
            $gameId,
            $playerId,
            $letter,
            $playedLetters,
            $remainingLives,
            $wordSoFar
        );

        $this->assertEquals($gameId, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());
        $this->assertEquals($letter, $event->getLetter());
        $this->assertEquals($playedLetters, $event->getPlayedLetters());
        $this->assertEquals($remainingLives, $event->getRemainingLives());
        $this->assertEquals($wordSoFar, $event->getWordSoFar());
        $this->assertEquals($wordSoFar, $event->getFeedback());
        $this->assertEquals(
            sprintf(
                'Well played! %s (letters played: %s) - Remaining chances: %d',
                $wordSoFar,
                implode(', ', $event->getPlayedLetters()),
                $event->getRemainingLives()
            ),
            $event->getAsMessage()
        );

        $this->assertEquals(
            array(
                'name' => 'hangman.letter.good',
                'gameId' => 666,
                'playerId' => 42,
                'letter' => $letter,
                'playedLetters' => $playedLetters,
                'remainingLives' => $remainingLives,
                'wordSoFar' => $wordSoFar
            ),
            $event->serialize()
        );

        $unserializedEvent = HangmanGoodLetterProposedEvent::deserialize(
            array(
                'name' => 'hangman.letter.good',
                'gameId' => 666,
                'playerId' => 42,
                'letter' => $letter,
                'playedLetters' => $playedLetters,
                'remainingLives' => $remainingLives,
                'wordSoFar' => $wordSoFar
            )
        );

        $this->assertEquals(666, (string)$unserializedEvent->getGameId());
        $this->assertEquals(42, (string)$unserializedEvent->getPlayerId());
        $this->assertEquals($letter, $unserializedEvent->getLetter());
        $this->assertEquals($playedLetters, $unserializedEvent->getPlayedLetters());
        $this->assertEquals($remainingLives, $unserializedEvent->getRemainingLives());
        $this->assertEquals($wordSoFar, $unserializedEvent->getWordSoFar());
    }
}