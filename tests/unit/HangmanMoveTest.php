<?php
namespace Hangman\Test;

use Hangman\Move\Answer;
use Hangman\Move\Proposition;

class HangmanMoveTest extends \PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testProposition()
    {
        $text = 'text';
        $move = new Proposition($text);

        $this->assertEquals($text, $move->getText());
    }

    /**
     * @test
     */
    public function testAnswer()
    {
        $text = 'text';
        $move = new Answer($text);

        $this->assertEquals($text, $move->getText());
    }
} 