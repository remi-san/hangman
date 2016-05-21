<?php

namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use Hangman\Event\Util\HangmanBasicResultEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\GameResult;

class HangmanPlayerTurnEvent extends HangmanBasicResultEvent implements GameResult, SerializableInterface
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.turn';

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     */
    public function __construct(
        MiniGameId $gameId,
        PlayerId $playerId
    ) {
        parent::__construct(self::NAME, $gameId, $playerId);
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return sprintf('It is your turn to play');
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return [
            'name' => self::NAME,
            'gameId' => (string) $this->getGameId(),
            'playerId' => (string) $this->getPlayerId()
        ];
    }

    /**
     * @param  array $data
     * @return HangmanPlayerLostEvent
     */
    public static function deserialize(array $data)
    {
        return new self(
            MiniGameId::create($data['gameId']),
            PlayerId::create($data['playerId'])
        );
    }
}
