<?php
namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use Hangman\Event\Util\HangmanErrorEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerTriedPlayingInactiveGameEvent extends HangmanErrorEvent implements SerializableInterface
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.inactive-game';

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
        return array(
            'name' => self::NAME,
            'gameId' => (string)$this->getGameId()->getId(),
            'playerId' => (string)$this->getPlayerId()->getId()
        );
    }

    /**
     * @param  array $data
     * @return HangmanPlayerTriedPlayingInactiveGameEvent
     */
    public static function deserialize(array $data)
    {
        return new self(
            new MiniGameId($data['gameId']),
            new PlayerId($data['playerId'])
        );
    }
}
