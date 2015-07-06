<?php
namespace Hangman\Test;

use Hangman\HangmanPlayer;
use Rhumsaa\Uuid\Uuid;

class HangmanPlayerTest extends \PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testId()
    {
        $id = 42;
        $name = 'Douglas';

        $player = new HangmanPlayer(null, $name);

        $this->assertTrue(Uuid::isValid($player->getId()));
        $this->assertEquals($name, $player->getName());
    }

    /**
     * @test
     */
    public function testGetters()
    {
        $id = 42;
        $name = 'Douglas';

        $player = new HangmanPlayer($id, $name);

        $this->assertEquals($id, $player->getId());
        $this->assertEquals($name, $player->getName());
    }
}