<?php
namespace Hangman\Test\Move;

use Hangman\Move\Answer;
use Hangman\Move\Proposition;

class HangmanMoveTest extends \PHPUnit_Framework_TestCase
{
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
        $move = Proposition::create($text);

        $this->assertEquals($text, $move->getText());
    }

    /**
     * @test
     */
    public function testAnswer()
    {
        $text = 'text';
        $move = Answer::create($text);

        $this->assertEquals($text, $move->getText());
    }
}
