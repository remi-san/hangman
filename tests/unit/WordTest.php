<?php

namespace Hangman\Test;

use Hangman\Word;

class WordTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $word;

    /** @var string[] */
    private $letters;

    /** @var Word */
    private $serviceUnderTest;

    /**
     * Init
     */
    public function setUp()
    {
        $this->word = 'Babibel';
        $this->letters = [ 'A', 'B', 'E', 'I', 'L' ];

        $this->serviceUnderTest = new Word($this->word);
    }

    /**
     * Close
     */
    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldReturnAllTheLettersOfTheWord()
    {
        $this->assertEquals($this->letters, $this->serviceUnderTest->getLetters());
    }

    /**
     * @test
     */
    public function itShouldAssertAnswerIsValidOrInvalid()
    {
        $this->assertTrue($this->serviceUnderTest->isValid('Abeille'));
        $this->assertTrue($this->serviceUnderTest->isValid('Papille'));
        $this->assertFalse($this->serviceUnderTest->isValid('Or'));
        $this->assertFalse($this->serviceUnderTest->isValid('Babylone'));
    }

    /**
     * @test
     */
    public function itShouldAssertWordAreEqualOrNot()
    {
        $this->assertTrue($this->serviceUnderTest->equals($this->word));
        $this->assertFalse($this->serviceUnderTest->equals('Peuplier'));
    }

    /**
     * @test
     */
    public function itShouldStringifyTheWord()
    {
        $this->assertEquals($this->word, (string) $this->serviceUnderTest, '', 0, 0, false, true);
    }

    /**
     * @test
     */
    public function itShouldAssertLettersAreIncludedInTheWord()
    {
        foreach ($this->letters as $letter) {
            $this->assertTrue($this->serviceUnderTest->contains($letter));
        }

        $this->assertFalse($this->serviceUnderTest->contains('Z'));
    }

    /**
     * @test
     */
    public function itShouldBuildTheIncompleteWord()
    {
        $this->assertEquals('B _ B _ B _ _', $this->serviceUnderTest->buildWord(['b']));
        $this->assertEquals('B A B _ B _ _', $this->serviceUnderTest->buildWord(['b', 'a']));
        $this->assertEquals('B A B I B E L', $this->serviceUnderTest->buildWord($this->letters));
    }
}
