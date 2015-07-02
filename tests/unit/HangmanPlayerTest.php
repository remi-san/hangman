<?php
namespace Hangman\Test;

use Hangman\HangmanPlayer;

class HangmanPlayerTest extends \PHPUnit_Framework_TestCase {

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