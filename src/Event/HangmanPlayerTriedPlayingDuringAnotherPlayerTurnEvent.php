<?php

namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use Hangman\Event\Util\HangmanErrorEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerTriedPlayingDuringAnotherPlayerTurnEvent extends HangmanErrorEvent implements SerializableInterface
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.wrong-turn';

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     */
    public function __construct(MiniGameId $gameId, PlayerId $playerId)
    {
        parent::__construct(self::NAME, $gameId, $playerId);
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return 'You cannot play.';
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
     * @return HangmanPlayerTriedPlayingDuringAnotherPlayerTurnEvent
     */
    public static function deserialize(array $data)
    {
        return new self(
            MiniGameId::create($data['gameId']),
            PlayerId::create($data['playerId'])
        );
    }
}
