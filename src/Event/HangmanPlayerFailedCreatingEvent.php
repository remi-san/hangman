<?php
namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use Hangman\Event\Util\HangmanErrorEvent;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerFailedCreatingEvent extends HangmanErrorEvent implements SerializableInterface
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.created';

    /**
     * @var string
     */
    private $externalReference;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param string     $externalReference
     */
    public function __construct(MiniGameId $gameId, PlayerId $playerId, $externalReference)
    {
        parent::__construct(self::NAME, $gameId, $playerId);
        $this->externalReference = $externalReference;
    }

    /**
     * @return string
     */
    public function getExternalReference()
    {
        return $this->externalReference;
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return 'You cannot add a player to a game that has already started.';
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'name' => self::NAME,
            'gameId' => (string)$this->getGameId()->getId(),
            'playerId' => (string)$this->getPlayerId()->getId(),
            'externalReference' => $this->externalReference
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
            new PlayerId($data['playerId']),
            $data['externalReference']
        );
    }
}
