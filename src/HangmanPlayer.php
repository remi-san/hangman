<?php
namespace Hangman;

use MiniGame\Player;

/**
 * @Entity(repositoryClass="\Hangman\Repository\HangmanPlayerRepository")
 * @Table(name="minigame.player")
 **/
class HangmanPlayer implements Player {

    /**
     * @var int
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $name;

    /**
     * Constructor
     *
     * @param int    $id
     * @param string $name
     */
    function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    /**
     * Returns the id of the player
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the name of the player
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
} 