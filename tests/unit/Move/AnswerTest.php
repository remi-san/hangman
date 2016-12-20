<?php
namespace Hangman\Test\Move;

use Hangman\Move\Answer;

class AnswerTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $text;

    public function setUp()
    {
        $this->text = 'text';
    }

    public function tearDown()
    {
        \Mockery::close();
    }
    /**
     * @test
     */
    public function itShouldBuildAnswer()
    {
        $move = Answer::create($this->text);

        $this->assertEquals($this->text, $move->getText());
    }
}
