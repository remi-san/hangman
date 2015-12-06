<?php
namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use Hangman\Event\Util\HangmanErrorEvent;
use Hangman\Move\Answer;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;
use MiniGame\Move;

class HangmanPlayerProposedInvalidAnswerEvent extends HangmanErrorEvent implements SerializableInterface
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.invalid-answer';

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
        parent::__construct(self::NAME, $gameId, $playerId);
        $this->answer = $answer;
    }

    /**
     * @return Move
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return 'Invalid answer';
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
            'answer' => (string)$this->answer->getText()
        );
    }

    /**
     * @param  array $data
     * @return HangmanPlayerProposedInvalidAnswerEvent
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
