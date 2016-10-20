<?php

namespace Hangman\Options;

use MiniGame\Exceptions\IllegalOptionException;
use MiniGame\GameOptions;
use MiniGame\Options\AbstractGameOptions;
use MiniGame\PlayerOptions;
use WordSelector\Entity\Word;

class HangmanOptions extends AbstractGameOptions implements GameOptions
{
    /**
     * @var int
     */
    private $lives;

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $level;

    /**
     * @var Word
     */
    private $word;

    /**
     * @var string
     */
    private $language;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getLives()
    {
        return $this->lives;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Check the options
     *
     * @throws IllegalOptionException
     */
    private function checkOptions()
    {
        if ($this->length !== null && $this->word) {
            throw new IllegalOptionException(
                "You can't set the length if the word is already chosen!",
                'length',
                $this->length
            );
        }

        if ($this->level !== null && $this->word) {
            throw new IllegalOptionException(
                "You can't set the level if the word is already chosen!",
                'level',
                $this->level
            );
        }

        if ($this->word === null && $this->level === null && $this->length === null) {
            throw new \InvalidArgumentException('You have to provide at least one option (word/length/level)!');
        }
    }

    /**
     * Static Constructor.
     *
     * @param  Word            $word
     * @param  string          $language
     * @param  int             $length
     * @param  int             $level
     * @param  int             $lives
     * @param  PlayerOptions[] $players
     *
     * @throws IllegalOptionException
     *
     * @return HangmanOptions
     */
    public static function create(
        Word $word = null,
        $language = 'en',
        $length = null,
        $level = null,
        $lives = 6,
        array $players = []
    ) {
        $obj = new self();

        $obj->init($players);
        $obj->lives = $lives;
        $obj->word = $word;
        $obj->language = $language;
        $obj->length = $length;
        $obj->level = $level;

        $obj->checkOptions();

        return $obj;
    }
}
