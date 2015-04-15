<?php
namespace Hangman\Test\Mock;

use Hangman\Hangman;
use Hangman\Options\HangmanOptions;

trait HangmanMocker {

    /**
     * Returns a hangman mini-game
     * @param  int $id
     * @return \Hangman\Hangman
     */
    public function getHangmanMiniGame($id)
    {
        $h = \Mockery::mock('\\Hangman\\Hangman');
        $h->shouldReceive('getId')->andReturn($id);

        return $h;
    }

    /**
     * @param  string $word
     * @param  int    $length
     * @param  int    $level
     * @param  array  $players
     * @return \Hangman\Options\HangmanOptions
     */
    public function getHangmanOptions($word = null, $length = null, $level = null, array $players = array())
    {
        $options = \Mockery::mock('\\Hangman\\Options\\HangmanOptions');

        $options->shouldReceive('getWord')->andReturn($word);
        $options->shouldReceive('getLength')->andReturn($length);
        $options->shouldReceive('getLevel')->andReturn($level);
        $options->shouldReceive('getPlayers')->andReturn($players);

        return $options;
    }
} 