<?php
namespace Hangman\Repository;

use Doctrine\ORM\EntityRepository;
use MiniGame\MiniGame;
use MiniGame\Repository\MiniGameRepository;

class HangmanRepository extends EntityRepository implements MiniGameRepository
{
    /**
     * Saves a mini game
     *
     * @param  MiniGame $game
     *
     * @return void
     */
    public function save(MiniGame $game)
    {
        $this->_em->persist($game);
    }

    /**
     * Deletes a mini game
     *
     * @param  MiniGame $game
     *
     * @return void
     */
    public function delete(MiniGame $game)
    {
        $this->_em->detach($game);
    }

}