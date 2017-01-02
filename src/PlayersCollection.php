<?php

namespace Hangman;

use Assert\Assertion;
use Doctrine\Common\Collections\ArrayCollection;
use Hangman\Entity\HangmanPlayer;
use MiniGame\Entity\PlayerId;

class PlayersCollection extends ArrayCollection
{
    /**
     * @var array
     */
    private $gameOrder;

    /**
     * @var HangmanPlayer
     */
    private $currentPlayer;

    /**
     * @inheritDoc
     */
    public function __construct(array $elements = array())
    {
        parent::__construct($elements);

        $this->gameOrder = [];
    }

    /**
     * @param mixed         $key
     * @param HangmanPlayer $value
     */
    public function set($key, $value)
    {
        Assertion::isInstanceOf($value, HangmanPlayer::class);
        Assertion::eq($key, (string) $value->getId());

        parent::set($key, $value);

        $this->gameOrder[] = $key;
    }

    /**
     * @param HangmanPlayer $value
     *
     * @return bool
     */
    public function add($value)
    {
        Assertion::isInstanceOf($value, HangmanPlayer::class);

        $this->set((string) $value->getId(), $value);

        return true;
    }

    /**
     * @return bool
     */
    public function hasPlayers()
    {
        return $this->count() > 0;
    }

    /**
     * @return bool
     */
    public function hasAtLeastOneActivePlayer()
    {
        foreach ($this->gameOrder as $gameOrder) {
            /** @var HangmanPlayer $player */
            $player = $this->get($gameOrder);

            if ($player->getState() === HangmanPlayer::STATE_IN_GAME) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the next player in line
     *
     * @return PlayerId
     */
    public function getNextPlayerId()
    {
        $nbPlayers = count($this->gameOrder);
        $currentPlayerId = (string) $this->currentPlayer->getId();
        $nextPlayerPosition = (array_search($currentPlayerId, $this->gameOrder) + 1) % $nbPlayers;

        $pos = $nextPlayerPosition;
        do {
            $player = $this->get($this->gameOrder[$pos]);

            if ($player->getState() === HangmanPlayer::STATE_IN_GAME) {
                return PlayerId::create($this->gameOrder[$pos]);
            }

            $pos = ($pos + 1) % $nbPlayers;
        } while ($pos !== $nextPlayerPosition);

        return null;
    }

    /**
     * @param PlayerId $playerId
     *
     * @return bool
     */
    public function canPlay(PlayerId $playerId)
    {
        return $this->isCurrentPlayer($playerId);
    }

    /**
     * @param PlayerId $playerId
     *
     * @return bool
     */
    public function isCurrentPlayer(PlayerId $playerId = null)
    {
        return $playerId !== null && $this->currentPlayer !== null && $this->currentPlayer->getId()->equals($playerId);
    }

    /**
     * @return HangmanPlayer
     */
    public function getCurrentPlayer()
    {
        return $this->currentPlayer;
    }

    /**
     * @param PlayerId $playerId
     */
    public function setCurrentPlayer(PlayerId $playerId = null)
    {
        $this->currentPlayer = ($playerId !== null) ? $this->get((string) $playerId) : null;
    }
}
