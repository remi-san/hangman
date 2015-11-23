<?php
namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use Hangman\Exception\HangmanException;
use League\Event\Event;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\Exceptions\InactiveGameException;

class HangmanPlayerTriedPlayingInactiveGameEvent extends Event implements SerializableInterface
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.inactive-game';

    /**
     * @var MiniGameId
     */
    private $gameId;

    /**
     * @var PlayerId
     */
    private $playerId;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     */
    public function __construct(MiniGameId $gameId, PlayerId $playerId)
    {
        parent::__construct(self::NAME);
        $this->gameId = $gameId;
        $this->playerId = $playerId;
    }

    /**
     * @return MiniGameId
     */
    public function getGameId()
    {
        return $this->gameId;
    }

    /**
     * @return PlayerId
     */
    public function getPlayerId()
    {
        return $this->playerId;
    }

    /**
     * Returns the appropriate exception
     *
     * @return InactiveGameException
     */
    public function getException()
    {
        return new InactiveGameException('You cannot play.');
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'name' => self::NAME,
            'gameId' => (string)$this->gameId,
            'playerId' => (string)$this->playerId
        );
    }

    /**
     * @param  array $data
     * @return HangmanPlayerCreatedEvent
     */
    public static function deserialize(array $data)
    {
        return new self(
            new MiniGameId($data['gameId']),
            new PlayerId($data['playerId'])
        );
    }
}
