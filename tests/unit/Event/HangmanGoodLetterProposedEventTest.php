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
    }

    /**
     * @test
     */
    public function testSerialize()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);
        $nextPlayerId = $this->getPlayerId(43);
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
            $wordSoFar,
            $nextPlayerId
        );

        $this->assertEquals(
            array(
                'name' => 'hangman.letter.good',
                'gameId' => $gameId->getId(),
                'playerId' => $playerId->getId(),
                'letter' => $letter,
                'playedLetters' => $playedLetters,
                'remainingLives' => $remainingLives,
                'wordSoFar' => $wordSoFar,
                'nextPlayerId' => $nextPlayerId->getId()
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
        $nextPlayerId = 43;
        $letter = 'A';
        $playedLetters = array('A');
        $remainingLives = 5;
        $wordSoFar = 'A _ _';

        $unserializedEvent = HangmanGoodLetterProposedEvent::deserialize(
            array(
                'name' => 'hangman.letter.good',
                'gameId' => $gameId,
                'playerId' => $playerId,
                'letter' => $letter,
                'playedLetters' => $playedLetters,
                'remainingLives' => $remainingLives,
                'wordSoFar' => $wordSoFar,
                'nextPlayerId' => $nextPlayerId
            )
        );

        $this->assertEquals($gameId, $unserializedEvent->getGameId()->getId());
        $this->assertEquals($playerId, $unserializedEvent->getPlayerId()->getId());
        $this->assertEquals($letter, $unserializedEvent->getLetter());
        $this->assertEquals($playedLetters, $unserializedEvent->getPlayedLetters());
        $this->assertEquals($remainingLives, $unserializedEvent->getRemainingLives());
        $this->assertEquals($wordSoFar, $unserializedEvent->getWordSoFar());
        $this->assertEquals($nextPlayerId, $unserializedEvent->getNextPlayerId()->getId());
    }
}
