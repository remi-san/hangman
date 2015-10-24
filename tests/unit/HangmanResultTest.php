<?php
namespace Hangman\Test;

use Hangman\Result\HangmanBadProposition;
use Hangman\Result\HangmanError;
use Hangman\Result\HangmanGoodProposition;
use Hangman\Result\HangmanLost;
use Hangman\Result\HangmanWon;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\Player;
use MiniGame\Entity\PlayerId;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanResultTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    /**
     * @var Player
     */
    private $player;

    /**
     * @var PlayerId
     */
    private $playerId;

    /**
     * @var MiniGameId
     */
    private $gameId;

    /**
     * @var string[]
     */
    private $lettersPlayed;

    public function setUp()
    {
        $this->playerId = $this->getPlayerId(42);
        $this->gameId = $this->getMiniGameId(666);
        $this->player = $this->getPlayer($this->playerId, 'Douglas');
        $this->lettersPlayed = array('A', 'E', 'Z');
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testError()
    {
        $remainingChances = 0;
        $message = 'error';

        $badProposition = new HangmanError(
            $this->gameId,
            $this->playerId,
            $message,
            $this->lettersPlayed,
            $remainingChances
        );

        $this->assertEquals($this->gameId, $badProposition->getGameId());
        $this->assertEquals($this->playerId, $badProposition->getPlayerId());
        $this->assertEquals($this->lettersPlayed, $badProposition->getPlayedLetters());
        $this->assertEquals($remainingChances, $badProposition->getRemainingLives());
        $this->assertEquals($message, $badProposition->getAsMessage());
    }
}
