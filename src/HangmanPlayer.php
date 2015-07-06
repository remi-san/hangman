<?php
namespace Hangman;

use MiniGame\Player;
use Rhumsaa\Uuid\Uuid;

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
     * @Column(type="string", unique="true")
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
        if ($id === null) {
            $id = Uuid::uuid4()->toString();
        }

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