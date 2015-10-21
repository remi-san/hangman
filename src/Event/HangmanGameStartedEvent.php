<?php
namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use League\Event\Event;
use MiniGame\Entity\MiniGameId;

class HangmanGameStartedEvent extends Event implements SerializableInterface
{
    /**
     * @var string
     */
    const NAME = 'hangman.started';

    /**
     * @var MiniGameId
     */
    private $gameId;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     */
    public function __construct(MiniGameId $gameId)
    {
        parent::__construct(self::NAME);
        $this->gameId = $gameId;
    }

    /**
     * @return MiniGameId
     */
    public function getGameId()
    {
        return $this->gameId;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'name' => self::NAME,
            'gameId' => $this->gameId->getId()
        );
    }

    /**
     * @param  array $data
     * @return HangmanGameStartedEvent
     */
    public static function deserialize(array $data)
    {
        return new self(
            new MiniGameId($data['gameId'])
        );
    }
}
