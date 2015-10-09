<?php
namespace Hangman\Test;

use Hangman\Options\HangmanPlayerOptions;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanPlayerOptionsTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    public function setUp()
    {
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
        $options = new HangmanPlayerOptions(
            $this->getPlayerId(42),
            'toto',
            6
        );

        $this->assertEquals(6, $options->getLives());
    }
}
