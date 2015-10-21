<?php
namespace Hangman\Test\Mock;

use Hangman\Entity\HangmanPlayer;
use Hangman\Move\Answer;
use Hangman\Move\Proposition;
use Hangman\Options\HangmanPlayerOptions;
use MiniGame\Entity\MiniGame;
use MiniGame\Entity\PlayerId;

trait HangmanMocker
{
    /**
     * Returns a hangman mini-game
     * @param  int $id
     * @return \Hangman\Entity\Hangman
     */
    public function getHangmanMiniGame($id)
    {
        $h = \Mockery::mock('\\Hangman\\Entity\\Hangman');
        $h->shouldReceive('getId')->andReturn($id);

        return $h;
    }

    /**
     * Returns a twitter player
     *
     * @param  PlayerId $id
     * @param  string   $name
     * @param  MiniGame $miniGame
     * @return HangmanPlayer
     */
    public function getHangmanPlayer($id = null, $name = null, MiniGame $miniGame = null)
    {
        $player = \Mockery::mock('\\Hangman\\Entity\\HangmanPlayer');
        $player->shouldReceive('getId')->andReturn($id);
        $player->shouldReceive('getName')->andReturn($name);
        $player->shouldReceive('setGame');
        $player->shouldReceive('getGame')->andReturn($miniGame);

        return $player;
    }

    /**
     * @param  string     $word
     * @param  string     $lang
     * @param  int        $length
     * @param  int        $level
     * @param  array      $players
     * @return \Hangman\Options\HangmanOptions
     */
    public function getHangmanOptions(
        $word = null,
        $lang = 'en',
        $length = null,
        $level = null,
        array $players = array()
    ) {
        $options = \Mockery::mock('\\Hangman\\Options\\HangmanOptions');

        $options->shouldReceive('getWord')->andReturn($word);
        $options->shouldReceive('getLength')->andReturn($length);
        $options->shouldReceive('getLevel')->andReturn($level);
        $options->shouldReceive('getPlayers')->andReturn($players);
        $options->shouldReceive('getLanguage')->andReturn($lang);

        return $options;
    }

    /**
     * @param  PlayerId $playerId
     * @param  string   $playerName
     * @param  int      $lives
     * @param  string   $externalReference
     * @return HangmanPlayerOptions
     */
    public function getHangmanPlayerOptions(
        PlayerId $playerId = null,
        $playerName = null,
        $lives = null,
        $externalReference = null
    ) {
        $options = \Mockery::mock('\\Hangman\\Options\\HangmanPlayerOptions');

        $options->shouldReceive('getPlayerId')->andReturn($playerId);
        $options->shouldReceive('getName')->andReturn($playerName);
        $options->shouldReceive('getLives')->andReturn($lives);
        $options->shouldReceive('getExternalReference')->andReturn($externalReference);

        return $options;
    }

    /**
     * @param  string $text
     * @return Answer
     */
    public function getAnswer($text)
    {
        $move = \Mockery::mock('\\Hangman\\Move\\Answer');
        $move->shouldReceive('getText')->andReturn($text);
        return $move;
    }

    /**
     * @param  string $text
     * @return Proposition
     */
    public function getProposition($text)
    {
        $move = \Mockery::mock('\\Hangman\\Move\\Proposition');
        $move->shouldReceive('getText')->andReturn($text);
        return $move;
    }
}
