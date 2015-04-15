<?php
namespace Hangman\Test;

use Hangman\Options\HangmanOptions;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanOptionsTest extends \PHPUnit_Framework_TestCase {
    use GameObjectMocker;

    private $players;

    private $lives;

    public function setUp()
    {
        $this->players = array(42=>$this->getPlayer(42, 'Douglas'));
        $this->lives = 5;
    }

    /**
     * @test
     */
    public function testHangmanOptionsWithWord()
    {
        $word    = 'word';
        $options = new HangmanOptions($word, null, null, $this->lives, $this->players);

        $this->assertEquals($word, $options->getWord());
        $this->assertNull($options->getLength());
        $this->assertNull($options->getLevel());
        $this->assertEquals($this->lives, $options->getLives());
        $this->assertEquals($this->players, $options->getPlayers());
    }

    /**
     * @test
     */
    public function testHangmanOptionsWithWordAndAddPlayer()
    {
        $word    = 'word';
        $options = new HangmanOptions($word, null, null, $this->lives, $this->players);

        $this->assertEquals($word, $options->getWord());
        $this->assertNull($options->getLength());
        $this->assertNull($options->getLevel());
        $this->assertEquals($this->lives, $options->getLives());
        $this->assertEquals($this->players, $options->getPlayers());

        $player2 = $this->getPlayer(43, 'Adams');
        $newPlayers = $this->players + array(43=>$player2);

        $options->addPlayer($player2);
        $this->assertEquals($newPlayers, $options->getPlayers());
    }

    /**
     * @test
     */
    public function testHangmanOptionsWithLength()
    {
        $length  = 5;
        $options = new HangmanOptions(null, $length, null, $this->lives, $this->players);

        $this->assertNull($options->getWord());
        $this->assertEquals($length, $options->getLength());
        $this->assertNull($options->getLevel());
        $this->assertEquals($this->lives, $options->getLives());
        $this->assertEquals($this->players, $options->getPlayers());
    }

    /**
     * @test
     */
    public function testHangmanOptionsWithLengthAndLevel()
    {
        $length  = 5;
        $level   = 5;
        $options = new HangmanOptions(null, $length, $level, $this->lives, $this->players);

        $this->assertNull($options->getWord());
        $this->assertEquals($length, $options->getLength());
        $this->assertEquals($level, $options->getLevel());
        $this->assertEquals($this->lives, $options->getLives());
        $this->assertEquals($this->players, $options->getPlayers());
    }

    /**
     * @test
     */
    public function testHangmanOptionsWithWordAndLength()
    {
        $word    = 'word';
        $length  = 5;

        $this->setExpectedException('\\MiniGame\\Exceptions\\IllegalOptionException');

        new HangmanOptions($word, $length, null, $this->lives, $this->players);
    }

    /**
     * @test
     */
    public function testHangmanOptionsWithWordAndLevel()
    {
        $word    = 'word';
        $level   = 5;

        $this->setExpectedException('\\MiniGame\\Exceptions\\IllegalOptionException');

        new HangmanOptions($word, null, $level, $this->lives, $this->players);
    }

    /**
     * @test
     */
    public function testHangmanOptionsWithWordLengthAndLevel()
    {
        $word    = 'word';
        $length  = 5;
        $level   = 5;

        $this->setExpectedException('\\MiniGame\\Exceptions\\IllegalOptionException');

        new HangmanOptions($word, $length, $level, $this->lives, $this->players);
    }

    /**
     * @test
     */
    public function testHangmanOptionsWithLengthAndLevelAndSetWord()
    {
        $word    = 'word';
        $length  = 5;
        $level   = 5;

        $this->setExpectedException('\\MiniGame\\Exceptions\\IllegalOptionException');

        $options = new HangmanOptions(null, $length, $level, $this->lives, $this->players);
        $options->setWord($word);
    }
} 