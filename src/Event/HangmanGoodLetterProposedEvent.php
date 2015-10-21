<?php
namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use League\Event\Event;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanGoodLetterProposedEvent extends Event implements SerializableInterface
{
    /**
     * @var string
     */
    const NAME = 'hangman.letter.good';

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
    private $letter;

    /**
     * @var string[]
     */
    private $playedLetters;

    /**
     * @var int
     */
    private $remainingLives;

    /**
     * @var string
     */
    private $wordSoFar;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param string     $letter
     * @param array      $playedLetters
     * @param int        $remainingLives
     * @param string     $wordSoFar
     */
    public function __construct(
        MiniGameId $gameId,
        PlayerId $playerId,
        $letter,
        array $playedLetters,
        $remainingLives,
        $wordSoFar
    ) {
        parent::__construct(self::NAME);
        $this->gameId = $gameId;
        $this->playerId = $playerId;
        $this->letter = $letter;
        $this->playedLetters = $playedLetters;
        $this->remainingLives = $remainingLives;
        $this->wordSoFar = $wordSoFar;
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
    public function getLetter()
    {
        return $this->letter;
    }

    /**
     * @return string[]
     */
    public function getPlayedLetters()
    {
        return $this->playedLetters;
    }

    /**
     * @return int
     */
    public function getRemainingLives()
    {
        return $this->remainingLives;
    }

    /**
     * @return string
     */
    public function getWordSoFar()
    {
        return $this->wordSoFar;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'name' => self::NAME,
            'gameId' => $this->gameId->getId(),
            'playerId' => $this->playerId->getId(),
            'letter' => $this->letter,
            'playedLetters' => $this->playedLetters,
            'remainingLives' => $this->remainingLives,
            'wordSoFar' => $this->wordSoFar
        );
    }

    /**
     * @param  array $data
     * @return HangmanGoodLetterProposedEvent
     */
    public static function deserialize(array $data)
    {
        return new self(
            new MiniGameId($data['gameId']),
            new PlayerId($data['playerId']),
            $data['letter'],
            $data['playedLetters'],
            $data['remainingLives'],
            $data['wordSoFar']
        );
    }
}
