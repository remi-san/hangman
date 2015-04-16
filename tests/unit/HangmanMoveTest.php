<?php
namespace Hangman\Test;

use Hangman\Move\Answer;
use Hangman\Move\Proposition;

class HangmanMoveTest extends \PHPUnit_Framework_TestCase {

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