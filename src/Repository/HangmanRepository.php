<?php
namespace Hangman\Repository;

use Doctrine\ORM\EntityRepository;
use MiniGame\MiniGame;
use MiniGame\Player;
use MiniGame\Repository\MiniGameRepository;

class HangmanRepository extends EntityRepository implements MiniGameRepository
{
    /**
     * Gets the mini-game for the player
     *
     * @param  Player $player
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findPlayerMinigame(Player $player)
    {
        $dql  = 'SELECT g, p ';
        $dql .= 'FROM '.$this->getClassName().' g ';
        $dql .= 'LEFT JOIN g.players p ';
        $dql .= 'WHERE p.id = ?1 ';

        return $this->getEntityManager()->createQuery($dql)
            ->setParameter(1, $player->getId())
            ->setMaxResults(1)
            ->getSingleResult();
    }

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
        $this->_em->remove($game);
    }

}