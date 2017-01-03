<?php

namespace Hangman\Test;

use Faker\Factory;
use Hangman\Entity\HangmanPlayer;
use Hangman\PlayersCollection;
use MiniGame\Entity\PlayerId;
use Mockery\Mock;

class PlayersCollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var PlayerId */
    private $playerOneId;

    /** @var PlayerId */
    private $playerTwoId;

    /** @var HangmanPlayer | Mock */
    private $playerOne;

    /** @var HangmanPlayer | Mock */
    private $playerTwo;
    
    /** @var PlayersCollection */
    private $collection;

    /**
     * Init
     */
    public function setUp()
    {
        $faker = Factory::create();

        $this->playerOneId = PlayerId::create($faker->uuid);
        $this->playerTwoId = PlayerId::create($faker->uuid);

        $this->playerOne = \Mockery::mock(HangmanPlayer::class);
        $this->playerTwo = \Mockery::mock(HangmanPlayer::class);

        $this->givenAFirstPlayer();
        $this->givenASecondPlayer();

        $this->collection = new PlayersCollection([$this->playerOne, $this->playerTwo]);
    }

    /**
     * Close
     */
    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function itShouldBeAbleToSetTheArrayByConstructorOrAdd()
    {
        $otherCollection = new PlayersCollection();
        $otherCollection->add($this->playerOne);
        $otherCollection->add($this->playerTwo);

        $this->assertEquals($this->collection, $otherCollection);
    }

    /**
     * @test
     */
    public function itShouldReturnTheSecondPlayerAsNextPlayer()
    {
        $this->givenFirstPlayerIsActive();
        $this->givenSecondPlayerIsActive();

        $nextPlayerId = $this->collection->getNextPlayerId();

        $this->assertEquals($this->playerTwoId, $nextPlayerId);
    }

    /**
     * @test
     */
    public function itShouldReturnTheSamePlayerAsNextPlayerIfSecondPLayerIsNotActive()
    {
        $this->givenFirstPlayerIsActive();
        $this->givenSecondPlayerIsNotActive();

        $nextPlayerId = $this->collection->getNextPlayerId();

        $this->assertEquals($this->playerOneId, $nextPlayerId);
    }

    /**
     * @test
     */
    public function itShouldReturnNoOneAsNextPlayerIfNoPLayerIsActive()
    {
        $this->givenFirstPlayerIsNotActive();
        $this->givenSecondPlayerIsNotActive();

        $nextPlayerId = $this->collection->getNextPlayerId();

        $this->assertNull($nextPlayerId);
    }

    /**
     * @test
     */
    public function itShouldAssertThereIsAnActivePlayer()
    {
        $this->givenFirstPlayerIsActive();
        $this->givenSecondPlayerIsNotActive();

        $this->assertTrue($this->collection->hasPlayers());
        $this->assertTrue($this->collection->hasAtLeastOneActivePlayer());
    }

    /**
     * @test
     */
    public function itShouldAssertThereIsNoActivePlayer()
    {
        $this->givenFirstPlayerIsNotActive();
        $this->givenSecondPlayerIsNotActive();

        $this->assertTrue($this->collection->hasPlayers());
        $this->assertFalse($this->collection->hasAtLeastOneActivePlayer());
    }

    /**
     * @test
     */
    public function itShouldAssertThereIsNoActivePlayerIfThereIsNoPlayer()
    {
        $collection = new PlayersCollection();

        $this->assertFalse($collection->hasPlayers());
        $this->assertFalse($collection->hasAtLeastOneActivePlayer());
    }

    /**
     * @test
     */
    public function itShouldAssertPlayerCanPlayIfHeIsTheCurrentPlayer()
    {
        $this->assertTrue($this->collection->canPlay($this->playerOneId));
        $this->assertFalse($this->collection->canPlay($this->playerTwoId));
        $this->assertTrue($this->collection->isCurrentPlayer($this->playerOneId));
        $this->assertFalse($this->collection->isCurrentPlayer($this->playerTwoId));

        $this->collection->setCurrentPlayer($this->playerTwoId);

        $this->assertFalse($this->collection->canPlay($this->playerOneId));
        $this->assertTrue($this->collection->canPlay($this->playerTwoId));
        $this->assertFalse($this->collection->isCurrentPlayer($this->playerOneId));
        $this->assertTrue($this->collection->isCurrentPlayer($this->playerTwoId));
    }

    /**
     * @test
     */
    public function itShouldAssertPlayerIsTheCurrentPlayer()
    {
        $this->assertEquals($this->playerOne, $this->collection->getCurrentPlayer());

        $this->collection->setCurrentPlayer($this->playerTwoId);

        $this->assertEquals($this->playerTwo, $this->collection->getCurrentPlayer());
    }

    private function givenAFirstPlayer()
    {
        $this->playerOne->shouldReceive('getId')->andReturn($this->playerOneId);
    }

    private function givenASecondPlayer()
    {
        $this->playerTwo->shouldReceive('getId')->andReturn($this->playerTwoId);
    }

    private function givenFirstPlayerIsActive()
    {
        $this->playerOne->shouldReceive('getState')->andReturn(HangmanPlayer::STATE_IN_GAME);
    }

    private function givenSecondPlayerIsActive()
    {
        $this->playerTwo->shouldReceive('getState')->andReturn(HangmanPlayer::STATE_IN_GAME);
    }

    private function givenFirstPlayerIsNotActive()
    {
        $this->playerOne->shouldReceive('getState')->andReturn(HangmanPlayer::STATE_LOST);
    }

    private function givenSecondPlayerIsNotActive()
    {
        $this->playerTwo->shouldReceive('getState')->andReturn(HangmanPlayer::STATE_LOST);
    }
}
