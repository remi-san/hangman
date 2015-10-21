<?php
namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use League\Event\Event;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerCreatedEvent extends Event implements SerializableInterface
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.created';

    /**
     * @var MiniGameId
     */
    private $gameId;

    /**
     * @var PlayerId
     */
    private $playerId;

    /**
     * @var string
     */
    private $playerName;

    /**
     * @var int
     */
    private $lives;

    /**
     * @var string
     */
    private $externalReference;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param string     $playerName
     * @param int        $lives
     * @param string     $externalReference
     */
    public function __construct(MiniGameId $gameId, PlayerId $playerId, $playerName, $lives, $externalReference)
    {
        parent::__construct(self::NAME);
        $this->gameId = $gameId;
        $this->playerId = $playerId;
        $this->playerName = $playerName;
        $this->lives = $lives;
        $this->externalReference = $externalReference;
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
     * @return string
     */
    public function getPlayerName()
    {
        return $this->playerName;
    }

    /**
     * @return int
     */
    public function getLives()
    {
        return $this->lives;
    }

    /**
     * @return string
     */
    public function getExternalReference()
    {
        return $this->externalReference;
    }


    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'name' => self::NAME,
            'gameId' => (string)$this->gameId,
            'playerId' => (string)$this->playerId,
            'playerName' => $this->playerName,
            'lives' => $this->lives,
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
            $data['playerName'],
            $data['lives'],
            $data['externalReference']
        );
    }
}
