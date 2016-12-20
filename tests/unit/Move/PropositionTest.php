<?php
namespace Hangman\Test\Move;

use Hangman\Move\Proposition;

class PropositionTest extends \PHPUnit_Framework_TestCase
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
    public function itShouldBuildProposition()
    {
        $move = Proposition::create($this->text);

        $this->assertEquals($this->text, $move->getText());
    }
}
