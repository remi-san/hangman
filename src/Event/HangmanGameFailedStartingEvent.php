<?php

namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use Hangman\Event\Util\HangmanErrorEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanGameFailedStartingEvent extends HangmanErrorEvent implements SerializableInterface
{
    /**
     * @var string
     */
    const NAME = 'hangman.starting.failed';

    /**
     * @var string
     */
    const BAD_STATE = 'alreadyStarted';

    /**
     * @var string
     */
    const NO_PLAYER = 'noPlayer';

    /**
     * @var string
     */
    private $reason;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param string     $reason
     */
    public function __construct(MiniGameId $gameId, PlayerId $playerId = null, $reason = '')
    {
        parent::__construct(self::NAME, $gameId, $playerId);
        $this->reason = $reason;
    }

    public function getAsMessage()
    {
        switch ($this->reason) {
            case self::BAD_STATE:
                return "You can't start a game that's already started or is over.";
            case self::NO_PLAYER:
                return "You can't start a game that has no player.";
            default:
                return "Game failed starting for unknown reasons";
        }
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return [
            'name' => self::NAME,
            'gameId' => (string) $this->getGameId(),
            'playerId' => ($this->getPlayerId()) ? (string) $this->getPlayerId() : null,
            'reason' => $this->reason
        ];
    }

    /**
     * @param  array $data
     * @return HangmanGameStartedEvent
     */
    public static function deserialize(array $data)
    {
        return new self(
            MiniGameId::create($data['gameId']),
            isset($data['playerId']) ? PlayerId::create($data['playerId']) : null,
            $data['reason']
        );
    }
}
