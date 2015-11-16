<?php
namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use Hangman\Exception\HangmanException;
use League\Event\Event;
use MiniGame\Entity\MiniGameId;

class HangmanGameFailedStartingEvent extends Event implements SerializableInterface
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
     * @var MiniGameId
     */
    private $gameId;

    /**
     * @var string
     */
    private $reason;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param string     $reason
     */
    public function __construct(MiniGameId $gameId, $reason)
    {
        parent::__construct(self::NAME);
        $this->gameId = $gameId;
        $this->reason = $reason;
    }

    /**
     * @return MiniGameId
     */
    public function getGameId()
    {
        return $this->gameId;
    }

    /**
     * Returns the appropriate exception
     *
     * @return HangmanException
     */
    public function getException()
    {
        switch ($this->reason) {
            case self::BAD_STATE:
                return new HangmanException("You can't start a game that's already started or is over.");
            case self::NO_PLAYER:
                return new HangmanException("You can't start a game that has no player.");
            default:
                return new HangmanException();
        }
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'name' => self::NAME,
            'gameId' => $this->gameId->getId(),
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
            $data['reason']
        );
    }
}
