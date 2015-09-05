<?php
namespace Hangman\Options;

use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\Player;
use MiniGame\Exceptions\IllegalOptionException;
use MiniGame\GameOptions;
use MiniGame\Options\AbstractGameOptions;

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
     * @param  string    $word
     * @param  string    $language
     * @param  int       $length
     * @param  int       $level
     * @param  int       $lives
     * @param  Player[]  $players
     * @throws IllegalOptionException
     */
    public function __construct(
        $word = null,
        $language = 'en',
        $length = null,
        $level = null,
        $lives = 6,
        array $players = array()
    ) {
        parent::__construct($players);

        $this->lives = $lives;
        $this->word = $word;
        $this->language = $language;
        $this->length = $length;
        $this->level = $level;

        $this->checkOptions();
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
    }
}
