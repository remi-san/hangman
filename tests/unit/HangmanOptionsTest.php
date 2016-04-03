<?php
namespace Hangman\Test;

use Hangman\Options\HangmanOptions;
use MiniGame\Test\Mock\GameObjectMocker;
use WordSelector\Entity\Word;

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
        $word    = \Mockery::mock(Word::class);
        $lang    = 'en';
        $options = HangmanOptions::create($word, $lang, null, null, $this->lives, $this->players);

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
        $options = HangmanOptions::create(null, 'en', $length, null, $this->lives, $this->players);

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
        $options = HangmanOptions::create(null, 'en', $length, $level, $this->lives, $this->players);

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
        $word    = \Mockery::mock(Word::class);
        $length  = 5;

        $this->setExpectedException('\\MiniGame\\Exceptions\\IllegalOptionException');

        HangmanOptions::create($word, 'en', $length, null, $this->lives, $this->players);
    }

    /**
     * @test
     */
    public function testHangmanOptionsWithWordAndLevel()
    {
        $word    = \Mockery::mock(Word::class);
        $level   = 5;

        $this->setExpectedException('\\MiniGame\\Exceptions\\IllegalOptionException');

        HangmanOptions::create($word, 'en', null, $level, $this->lives, $this->players);
    }

    /**
     * @test
     */
    public function testHangmanOptionsWithWordLengthAndLevel()
    {
        $word    = \Mockery::mock(Word::class);
        $length  = 5;
        $level   = 5;

        $this->setExpectedException('\\MiniGame\\Exceptions\\IllegalOptionException');

        HangmanOptions::create($word, 'en', $length, $level, $this->lives, $this->players);
    }
}
