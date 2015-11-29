<?php
namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use Hangman\Event\Util\HangmanBasicResultEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanGameStartedEvent extends HangmanBasicResultEvent implements SerializableInterface
{
    /**
     * @var string
     */
    const NAME = 'hangman.started';

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     */
    public function __construct(MiniGameId $gameId, PlayerId $playerId = null)
    {
        parent::__construct(self::NAME, $gameId, $playerId);
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return 'Game started';
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'name' => self::NAME,
            'gameId' => $this->getGameId()->getId(),
            'playerId' => ($this->getPlayerId()) ? $this->getPlayerId()->getId() : null
        );
    }

    /**
     * @param  array $data
     * @return HangmanGameStartedEvent
     */
    public static function deserialize(array $data)
    {
        return new self(
            new MiniGameId($data['gameId']),
            isset($data['playerId']) ? new PlayerId($data['playerId']) : null
        );
    }
}
