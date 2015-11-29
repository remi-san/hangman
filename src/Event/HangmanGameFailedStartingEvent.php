<?php
namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use Hangman\Event\Util\HangmanErrorEvent;
use Hangman\Exception\HangmanException;
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
     * Returns the appropriate exception
     *
     * @return HangmanException
     */
    public function getException()
    {
        return new HangmanException($this->getAsMessage());
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'name' => self::NAME,
            'gameId' => $this->getGameId()->getId(),
            'playerId' => ($this->getPlayerId()) ? $this->getPlayerId()->getId() : null,
            'reason' => $this->reason
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
            isset($data['playerId']) ? new PlayerId($data['playerId']) : null,
            $data['reason']
        );
    }
}
