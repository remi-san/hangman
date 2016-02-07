<?php
namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use Hangman\Event\Util\HangmanBasicResultEvent;
use MiniGame\Entity\MiniGameId;

class HangmanGameCreatedEvent extends HangmanBasicResultEvent implements SerializableInterface
{
    /**
     * @var string
     */
    const NAME = 'hangman.created';

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
        parent::__construct(self::NAME, $gameId);
        $this->word = $word;
    }

    /**
     * @return string
     */
    public function getWord()
    {
        return $this->word;
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return 'Game created';
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'name' => self::NAME,
            'gameId' => (string) $this->getGameId(),
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
            MiniGameId::create($data['gameId']),
            $data['word']
        );
    }
}
