<?php
namespace Hangman\Test\Options;

use Hangman\Options\HangmanOptions;
use Hangman\Options\HangmanPlayerOptions;
use MiniGame\Entity\Player;
use MiniGame\Exceptions\IllegalOptionException;
use WordSelector\Entity\Word;

class HangmanOptionsTest extends \PHPUnit_Framework_TestCase
{
    /** @var Word */
    private $word;

    /** @var string */
    private $lang;

    /** @var int */
    private $length;

    /** @var int */
    private $level;

    /** @var int */
    private $lives;

    /** @var Player[] */
    private $players;

    public function setUp()
    {
        $this->word    = \Mockery::mock(Word::class);
        $this->lang    = 'en';
        $this->length  = 5;
        $this->level   = 5;
        $this->lives = 5;

        $this->players = [ \Mockery::mock(HangmanPlayerOptions::class) ];
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
        $options = HangmanOptions::create($this->word, $this->lang, null, null, $this->lives, $this->players);

        $this->assertEquals($this->word, $options->getWord());
        $this->assertNull($options->getLength());
        $this->assertNull($options->getLevel());
        $this->assertEquals($this->lives, $options->getLives());
        $this->assertEquals($this->players, $options->getPlayerOptions());
        $this->assertEquals($this->lang, $options->getLanguage());
    }

    /**
     * @test
     */
    public function itShouldCreateHangmanOptionsWithLength()
    {
        $options = HangmanOptions::create(null, $this->lang, $this->length, null, $this->lives, $this->players);

        $this->assertNull($options->getWord());
        $this->assertEquals($this->length, $options->getLength());
        $this->assertNull($options->getLevel());
        $this->assertEquals($this->lives, $options->getLives());
        $this->assertEquals($this->players, $options->getPlayerOptions());
    }

    /**
     * @test
     */
    public function itShouldCreateHangmanOptionsWithLengthAndLevel()
    {
        $options = HangmanOptions::create(null, $this->lang, $this->length, $this->level, $this->lives, $this->players);

        $this->assertNull($options->getWord());
        $this->assertEquals($this->length, $options->getLength());
        $this->assertEquals($this->level, $options->getLevel());
        $this->assertEquals($this->lives, $options->getLives());
        $this->assertEquals($this->players, $options->getPlayerOptions());
    }

    /**
     * @test
     */
    public function itShouldFailCreatingHangmanOptionsWithWordAndLength()
    {
        $this->setExpectedException(IllegalOptionException::class);

        HangmanOptions::create($this->word, $this->lang, $this->length, null, $this->lives, $this->players);
    }

    /**
     * @test
     */
    public function itShouldFailCreatingHangmanOptionsWithWordAndLevel()
    {
        $this->setExpectedException(IllegalOptionException::class);

        HangmanOptions::create($this->word, $this->lang, null, $this->level, $this->lives, $this->players);
    }

    /**
     * @test
     */
    public function itShouldFailCreatingHangmanOptionsWithWordLengthAndLevel()
    {
        $this->setExpectedException(IllegalOptionException::class);

        HangmanOptions::create($this->word, $this->lang, $this->length, $this->level, $this->lives, $this->players);
    }

    /**
     * @test
     */
    public function itShouldFailCreatingHangmanOptionsWithoutWordLengthAndLevel()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        HangmanOptions::create(null, $this->lang, null, null, $this->lives, $this->players);
    }
}
