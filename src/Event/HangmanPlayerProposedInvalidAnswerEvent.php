<?php
namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use Hangman\Move\Answer;
use League\Event\Event;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\Exceptions\IllegalMoveException;
use MiniGame\Move;

class HangmanPlayerProposedInvalidAnswerEvent extends Event implements SerializableInterface
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.invalid-answer';

    /**
     * @var MiniGameId
     */
    private $gameId;

    /**
     * @var PlayerId
     */
    private $playerId;

    /**
     * @var Answer
     */
    private $answer;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param Answer     $answer
     */
    public function __construct(MiniGameId $gameId, PlayerId $playerId, Answer $answer)
    {
        parent::__construct(self::NAME);
        $this->gameId = $gameId;
        $this->playerId = $playerId;
        $this->answer = $answer;
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
     * @return Move
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * Returns the appropriate exception
     *
     * @return IllegalMoveException
     */
    public function getException()
    {
        return new IllegalMoveException($this->answer, 'Invalid answer');
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
            'answer' => (string)$this->answer->getText()
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
            new Answer($data['answer'])
        );
    }
}
