<?php
namespace Hangman\Test;

use Hangman\Options\HangmanOptions;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanOptionsTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    private $players;

    private $lives;

    private $id;

    public function setUp()
    {
        $this->id = $this->getMiniGameId(666);
        $this->players = array(42=>$this->getPlayer(42, 'Douglas'));
        $this->lives = 5;
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testHangmanOptionsWithWord()
    {
        $word    = 'word';
        $lang = 'en';
        $options = new HangmanOptions($word, $lang, null, null, $this->lives, $this->players);

        $this->assertEquals($word, $options->getWord());
        $this->assertNull($options->getLength());
        $this->assertNull($options->getLevel());
        $this->assertEquals($this->lives, $options->getLives());
        $this->assertEquals($this->players, $options->getPlayerOptions());
        $this->assertEquals($lang, $options->getLanguage());
    }

    /**
     * @test
     */
    public function testHangmanOptionsWithLength()
    {
        $length  = 5;
        $options = new HangmanOptions(null, 'en', $length, null, $this->lives, $this->players);

        $this->assertNull($options->getWord());
        $this->assertEquals($length, $options->getLength());
        $this->assertNull($options->getLevel());
        $this->assertEquals($this->lives, $options->getLives());
        $this->assertEquals($this->players, $options->getPlayerOptions());
    }

    /**
     * @test
     */
    public function testHangmanOptionsWithLengthAndLevel()
    {
        $length  = 5;
        $level   = 5;
        $options = new HangmanOptions(null, 'en', $length, $level, $this->lives, $this->players);

        $this->assertNull($options->getWord());
        $this->assertEquals($length, $options->getLength());
        $this->assertEquals($level, $options->getLevel());
        $this->assertEquals($this->lives, $options->getLives());
        $this->assertEquals($this->players, $options->getPlayerOptions());
    }

    /**
     * @test
     */
    public function testHangmanOptionsWithWordAndLength()
    {
        $word    = 'word';
        $length  = 5;

        $this->setExpectedException('\\MiniGame\\Exceptions\\IllegalOptionException');

        new HangmanOptions($word, 'en', $length, null, $this->lives, $this->players);
    }

    /**
     * @test
     */
    public function testHangmanOptionsWithWordAndLevel()
    {
        $word    = 'word';
        $level   = 5;

        $this->setExpectedException('\\MiniGame\\Exceptions\\IllegalOptionException');

        new HangmanOptions($word, 'en', null, $level, $this->lives, $this->players);
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

        new HangmanOptions($word, 'en', $length, $level, $this->lives, $this->players);
    }
}
