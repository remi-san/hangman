<?php
namespace Hangman\Test\Options;

use Hangman\Options\HangmanPlayerOptions;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerOptionsTest extends \PHPUnit_Framework_TestCase
{
    /** @var PlayerId */
    private $playerId;

    /** @var MiniGameId */
    private $gameId;

    /** @var string */
    private $name;

    /** @var int */
    private $lives;

    /** @var string */
    private $externalReference;

    public function setUp()
    {
        $this->playerId = PlayerId::create(42);
        $this->gameId = MiniGameId::create(666);
        $this->name = 'toto';
        $this->lives = 6;
        $this->externalReference = 'ext';
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldCreateHangmanOptionsWithWord()
    {
        $options = HangmanPlayerOptions::create(
            $this->playerId,
            $this->gameId,
            $this->name,
            $this->lives,
            $this->externalReference
        );

        $this->assertEquals($this->playerId, $options->getPlayerId());
        $this->assertEquals($this->gameId, $options->getGameId());
        $this->assertEquals($this->name, $options->getName());
        $this->assertEquals($this->lives, $options->getLives());
        $this->assertEquals($this->externalReference, $options->getExternalReference());
    }
}
