<?php
namespace Hangman\Event;

use Broadway\Serializer\SerializableInterface;
use Hangman\Result\HangmanLost;
use MiniGame\Entity\MiniGameId;
use MiniGame\Entity\PlayerId;

class HangmanPlayerLostEvent extends HangmanResultEvent implements HangmanLost, SerializableInterface
{
    /**
     * @var string
     */
    const NAME = 'hangman.player.lost';

    /**
     * @var string
     */
    private $wordFound;

    /**
     * @var string
     */
    private $word;

    /**
     * Constructor
     *
     * @param MiniGameId $gameId
     * @param PlayerId   $playerId
     * @param array      $playedLetters
     * @param int        $remainingLives
     * @param string     $wordFound
     * @param string     $word
     */
    public function __construct(
        MiniGameId $gameId,
        PlayerId $playerId,
        array $playedLetters,
        $remainingLives,
        $wordFound,
        $word
    ) {
        parent::__construct(self::NAME, $gameId, $playerId, $playedLetters, $remainingLives);
        $this->wordFound = $wordFound;
        $this->word = $word;
    }

    /**
     * @return string
     */
    public function getWordFound()
    {
        return $this->wordFound;
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
    public function getSolution()
    {
        return $this->word;
    }

    /**
     * @return string
     */
    public function getAsMessage()
    {
        return sprintf('You lose... The word was %s.', $this->getWord());
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
            'playedLetters' => $this->getPlayedLetters(),
            'remainingLives' => $this->getRemainingLives(),
            'wordFound' => $this->wordFound,
            'word' => $this->word
        );
    }

    /**
     * @param  array $data
     * @return HangmanPlayerLostEvent
     */
    public static function deserialize(array $data)
    {
        return new self(
            new MiniGameId($data['gameId']),
            new PlayerId($data['playerId']),
            $data['playedLetters'],
            $data['remainingLives'],
            $data['wordFound'],
            $data['word']
        );
    }
}
