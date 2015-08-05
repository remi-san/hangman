<?php
namespace Hangman\Repository;

use Doctrine\ORM\EntityRepository;
use MiniGame\Entity\Player;
use MiniGame\Repository\PlayerRepository;

class HangmanPlayerRepository extends EntityRepository implements PlayerRepository
{
    /**
     * Saves a player
     *
     * @param  Player $player
     *
     * @return void
     */
    public function save(Player $player)
    {
        $this->_em->persist($player);
    }

    /**
     * Deletes a player
     *
     * @param  Player $player
     *
     * @return void
     */
    public function delete(Player $player)
    {
        $this->_em->remove($player);
        $this->_em->flush();
    }
}
