<?php
namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use League\Event\Event;
use MiniGame\Entity\MiniGameId;

class HangmanGameCreatedEvent extends Event implements SerializableInterface
{
    /**
     * @var string
     */
    const NAME = 'hangman.created';

    /**
     * @var MiniGameId
     */
    private $gameId;

    /**
     * @var string
     */
    private $word;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param string     $word
     */
    public function __construct(MiniGameId $gameId, $word)
    {
        parent::__construct(self::NAME);
        $this->gameId = $gameId;
        $this->word = $word;
    }

    /**
     * @return MiniGameId
     */
    public function getGameId()
    {
        return $this->gameId;
    }

    /**
     * @return string
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'name' => self::NAME,
            'gameId' => $this->gameId->getId(),
            'word' => $this->word
        );
    }

    /**
     * @param  array $data
     * @return HangmanGameCreatedEvent
     */
    public static function deserialize(array $data)
    {
        return new self(
            new MiniGameId($data['gameId']),
            $data['word']
        );
    }
}
