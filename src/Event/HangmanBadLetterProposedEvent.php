<?php
namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use Hangman\Event\Util\HangmanResultEvent;
use Hangman\Result\HangmanBadProposition;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanBadLetterProposedEvent extends HangmanResultEvent implements HangmanBadProposition, SerializableInterface
{
    /**
     * @var string
     */
    const NAME = 'hangman.letter.bad';

    /**
     * @var string
     */
    private $letter;

    /**
     * @var int
     */
    private $livesLost;

    /**
     * @var string
     */
    private $wordSoFar;

    /**
     * @var PlayerId
     */
    private $nextPlayerId;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param string     $letter
     * @param array      $playedLetters
     * @param int        $livesLost
     * @param int        $remainingLives
     * @param string     $wordSoFar
     * @param PlayerId   $nextPlayerId
     */
    public function __construct(
        MiniGameId $gameId,
        PlayerId $playerId,
        $letter,
        array $playedLetters,
        $livesLost,
        $remainingLives,
        $wordSoFar,
        PlayerId $nextPlayerId = null
    ) {
        parent::__construct(self::NAME, $gameId, $playerId, $playedLetters, $remainingLives);
        $this->letter = $letter;
        $this->livesLost = $livesLost;
        $this->wordSoFar = $wordSoFar;
        $this->nextPlayerId = $nextPlayerId;
    }

    /**
     * @return string
     */
    public function getLetter()
    {
        return $this->letter;
    }

    /**
     * @return int
     */
    public function getLivesLost()
    {
        return $this->livesLost;
    }

    /**
     * @return string
     */
    public function getWordSoFar()
    {
        return $this->wordSoFar;
    }

    /**
     * @return string
     */
    public function getFeedback()
    {
        return $this->wordSoFar;
    }

    /**
     * @return PlayerId
     */
    public function getNextPlayerId()
    {
        return $this->nextPlayerId;
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return sprintf(
            'Too bad... %s (letters played: %s) - Remaining chances: %d',
            $this->getWordSoFar(),
            implode(', ', $this->getPlayedLetters()),
            $this->getRemainingLives()
        );
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return array(
            'name' => self::NAME,
            'gameId' => $this->getGameId()->getId(),
            'playerId' => $this->getPlayerId()->getId(),
            'letter' => $this->letter,
            'playedLetters' => $this->getPlayedLetters(),
            'livesLost' => $this->livesLost,
            'remainingLives' => $this->getRemainingLives(),
            'wordSoFar' => $this->wordSoFar,
            'nextPlayerId' => $this->getNextPlayerId()->getId()
        );
    }

    /**
     * @param  array $data
     * @return HangmanBadLetterProposedEvent
     */
    public static function deserialize(array $data)
    {
        return new self(
            new MiniGameId($data['gameId']),
            new PlayerId($data['playerId']),
            $data['letter'],
            $data['playedLetters'],
            $data['livesLost'],
            $data['remainingLives'],
            $data['wordSoFar'],
            new PlayerId($data['nextPlayerId'])
        );
    }
}
