<?php
namespace Hangman\Options;

use MiniGame\Exceptions\IllegalOptionException;
use MiniGame\GameOptions;
use MiniGame\Options\AbstractGameOptions;
use MiniGame\Player;

class HangmanOptions extends AbstractGameOptions implements GameOptions {

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $level;

    /**
     * @var string
     */
    private $word;

    /**
     * @var string
     */
    private $language;

    /**
     * Constructor
     *
     * @param  string   $word
     * @param  string   $language
     * @param  int      $length
     * @param  int      $level
     * @param  int      $lives
     * @param  Player[] $players
     * @throws IllegalOptionException
     */
    public function __construct($word = null, $language = 'en', $length = null, $level = null, $lives = 6, array $players = array()) {
        parent::__construct($lives, $players);
        $this->setWord($word);
        $this->setLanguage($language);
        $this->setLength($length);
        $this->setLevel($level);
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param  int $length
     * @throws IllegalOptionException
     */
    public function setLength($length)
    {
        if ($length !== null && $this->word) {
            throw new IllegalOptionException("You can't set the length if the word is already chosen!", 'length', $length);
        }

        $this->length = $length;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param  int $level
     * @throws IllegalOptionException
     */
    public function setLevel($level)
    {
        if ($level !== null && $this->word) {
            throw new IllegalOptionException("You can't set the level if the word is already chosen!", 'level', $level);
        }

        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * @param  string $word
     * @throws IllegalOptionException
     */
    public function setWord($word)
    {
        if ($word !== null && ($this->length || $this->level)) {
            throw new IllegalOptionException("You can't set the word if the level and/or the length are already chosen!", 'word', $word);
        }

        $this->word = $word;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }
} 