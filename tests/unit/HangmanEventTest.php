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
    }

    /**
     * @test
     */
    public function testHangmanStarted()
    {
        $id = $this->getMiniGameId(666);

        $event = new HangmanGameStartedEvent($id);

        $this->assertEquals($id, $event->getGameId());
    }

    /**
     * @test
     */
    public function testPlayerCreated()
    {
        $id = $this->getMiniGameId(666);
        $player = $this->getPlayer();

        $event = new HangmanPlayerCreatedEvent($id, $player);

        $this->assertEquals($id, $event->getGameId());
        $this->assertEquals($player, $event->getPlayer());
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
    }
}
