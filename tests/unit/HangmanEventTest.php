<?php
namespace Hangman\Test;

use Hangman\Event\HangmanBadLetterProposedEvent;
use Hangman\Event\HangmanGameCreatedEvent;
use Hangman\Event\HangmanGameStartedEvent;
use Hangman\Event\HangmanGoodLetterProposedEvent;
use Hangman\Event\HangmanPlayerCreatedEvent;
use Hangman\Event\HangmanPlayerDeletedEvent;
use Hangman\Event\HangmanPlayerLostEvent;
use Hangman\Event\HangmanPlayerWinEvent;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanEventTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testHangmanCreated()
    {
        $id = $this->getMiniGameId(666);
        $word = 'TEST';

        $event = new HangmanGameCreatedEvent($id, $word);

        $this->assertEquals($id, $event->getGameId());
        $this->assertEquals($word, $event->getWord());

        $this->assertEquals(
            array(
                'name' => 'hangman.created',
                'gameId' => 666,
                'word' => $word
            ),
            $event->serialize()
        );

        $unserializedEvent = HangmanGameCreatedEvent::deserialize(
            array(
                'name' => 'hangman.created',
                'gameId' => 666,
                'word' => $word
            )
        );

        $this->assertEquals(666, (string)$unserializedEvent->getGameId());
        $this->assertEquals($word, $unserializedEvent->getWord());
    }

    /**
     * @test
     */
    public function testHangmanStarted()
    {
        $id = $this->getMiniGameId(666);

        $event = new HangmanGameStartedEvent($id);

        $this->assertEquals($id, $event->getGameId());

        $this->assertEquals(
            array(
                'name' => 'hangman.started',
                'gameId' => 666
            ),
            $event->serialize()
        );

        $unserializedEvent = HangmanGameStartedEvent::deserialize(
            array(
                'name' => 'hangman.started',
                'gameId' => 666
            )
        );

        $this->assertEquals(666, (string)$unserializedEvent->getGameId());
    }

    /**
     * @test
     */
    public function testPlayerCreated()
    {
        $gameId = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);

        $event = new HangmanPlayerCreatedEvent($gameId, $playerId, 'name', 6, 'ext');

        $this->assertEquals($gameId, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());
        $this->assertEquals('name', $event->getPlayerName());
        $this->assertEquals(6, $event->getLives());
        $this->assertEquals('ext', $event->getExternalReference());

        $this->assertEquals(
            array(
                'name' => 'hangman.player.created',
                'gameId' => 666,
                'playerId' => 42,
                'playerName' => 'name',
                'lives' => 6,
                'externalReference' => 'ext'
            ),
            $event->serialize()
        );

        $unserializedEvent = HangmanPlayerCreatedEvent::deserialize(
            array(
                'name' => 'hangman.player.created',
                'gameId' => 666,
                'playerId' => 42,
                'playerName' => 'name',
                'lives' => 6,
                'externalReference' => 'ext'
            )
        );

        $this->assertEquals(666, (string)$unserializedEvent->getGameId());
        $this->assertEquals(42, (string)$unserializedEvent->getPlayerId());
        $this->assertEquals('name', $unserializedEvent->getPlayerName());
        $this->assertEquals(6, $unserializedEvent->getLives());
        $this->assertEquals('ext', $unserializedEvent->getExternalReference());
    }

    /**
     * @test
     */
    public function testPlayerDeleted()
    {
        $id = $this->getMiniGameId(666);
        $playerId = $this->getPlayerId(42);

        $event = new HangmanPlayerDeletedEvent($id, $playerId);

        $this->assertEquals($id, $event->getGameId());
        $this->assertEquals($playerId, $event->getPlayerId());

        $this->assertEquals(
            array(
                'name' => 'hangman.player.deleted',
                'gameId' => 666,
                'playerId' => 42
            ),
            $event->serialize()
        );

        $unserializedEvent = HangmanPlayerDeletedEvent::deserialize(
            array(
                'name' => 'hangman.player.deleted',
                'gameId' => 666,
                'playerId' => 42
            )
        );

        $this->assertEquals(666, (string)$unserializedEvent->getGameId());
        $this->assertEquals(42, (string)$unserializedEvent->getPlayerId());
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
        $this->assertEquals(
            sprintf(
                'Too bad... %s (letters played: %s) - Remaining chances: %d',
                $wordSoFar,
                implode(', ', $event->getPlayedLetters()),
                $event->getRemainingLives()
            ),
            $event->getAsMessage()
        );

        $this->assertEquals(
            array(
                'name' => 'hangman.letter.bad',
                'gameId' => 666,
                'playerId' => 42,
                'letter' => $letter,
                'playedLetters' => $playedLetters,
                'livesLost' => $livesLost,
                'remainingLives' => $remainingLives,
                'wordSoFar' => $wordSoFar
            ),
            $event->serialize()
        );

        $unserializedEvent = HangmanBadLetterProposedEvent::deserialize(
            array(
                'name' => 'hangman.letter.bad',
                'gameId' => 666,
                'playerId' => 42,
                'letter' => $letter,
                'playedLetters' => $playedLetters,
                'livesLost' => $livesLost,
                'remainingLives' => $remainingLives,
                'wordSoFar' => $wordSoFar
            )
        );

        $this->assertEquals(666, (string)$unserializedEvent->getGameId());
        $this->assertEquals(42, (string)$unserializedEvent->getPlayerId());
        $this->assertEquals($letter, $unserializedEvent->getLetter());
        $this->assertEquals($playedLetters, $unserializedEvent->getPlayedLetters());
        $this->assertEquals($livesLost, $unserializedEvent->getLivesLost());
        $this->assertEquals($remainingLives, $unserializedEvent->getRemainingLives());
        $this->assertEquals($wordSoFar, $unserializedEvent->getWordSoFar());
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

        $this->assertEquals(
            array(
                'name' => 'hangman.player.lost',
                'gameId' => 666,
                'playerId' => 42,
                'playedLetters' => $playedLetters,
                'remainingLives' => $remainingLives,
                'wordFound' => $wordSoFar,
                'word' => $word
            ),
            $event->serialize()
        );

        $unserializedEvent = HangmanPlayerLostEvent::deserialize(
            array(
                'name' => 'hangman.player.lost',
                'gameId' => 666,
                'playerId' => 42,
                'playedLetters' => $playedLetters,
                'remainingLives' => $remainingLives,
                'wordFound' => $wordSoFar,
                'word' => $word
            )
        );

        $this->assertEquals(666, (string)$unserializedEvent->getGameId());
        $this->assertEquals(42, (string)$unserializedEvent->getPlayerId());
        $this->assertEquals($playedLetters, $unserializedEvent->getPlayedLetters());
        $this->assertEquals($remainingLives, $unserializedEvent->getRemainingLives());
        $this->assertEquals($wordSoFar, $unserializedEvent->getWordFound());
        $this->assertEquals($word, $unserializedEvent->getWord());
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

        $this->assertEquals(
            array(
                'name' => 'hangman.player.win',
                'gameId' => 666,
                'playerId' => 42,
                'playedLetters' => $playedLetters,
                'remainingLives' => $remainingLives,
                'word' => $word
            ),
            $event->serialize()
        );

        $unserializedEvent = HangmanPlayerWinEvent::deserialize(
            array(
                'name' => 'hangman.player.win',
                'gameId' => 666,
                'playerId' => 42,
                'playedLetters' => $playedLetters,
                'remainingLives' => $remainingLives,
                'word' => $word
            )
        );

        $this->assertEquals(666, (string)$unserializedEvent->getGameId());
        $this->assertEquals(42, (string)$unserializedEvent->getPlayerId());
        $this->assertEquals($playedLetters, $unserializedEvent->getPlayedLetters());
        $this->assertEquals($remainingLives, $unserializedEvent->getRemainingLives());
        $this->assertEquals($word, $unserializedEvent->getWord());
    }
}
