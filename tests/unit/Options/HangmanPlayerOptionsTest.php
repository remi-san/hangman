<?php
namespace Hangman\Test\Options;

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
        $options = HangmanPlayerOptions::create(
            $this->getPlayerId(42),
            $this->getMiniGameId(666),
            'toto',
            6,
            'ext'
        );

        $this->assertEquals(6, $options->getLives());
        $this->assertEquals('ext', $options->getExternalReference());
    }
}
