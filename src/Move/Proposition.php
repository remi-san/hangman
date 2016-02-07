<?php
namespace Hangman\Move;

use MiniGame\Move;

class Proposition implements Move
{
    /**
     * @var string
     */
    private $text;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Static constructor.
     *
     * @param $text
     *
     * @return Proposition
     */
    public static function create($text)
    {
        $obj = new self();

        $obj->text = $text;

        return $obj;
    }
}
