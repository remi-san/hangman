<?php
namespace Hangman\Test;

use Doctrine\ORM\Query;
use Hangman\Repository\HangmanRepository;

class HangmanRepositoryTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function testSaveDelete() {

        $hangman = \Mockery::mock('\\Hangman\\Hangman');

        $entityManager = \Mockery::mock('\\Doctrine\\ORM\\EntityManager');
        $entityManager->shouldReceive('persist')->with($hangman)->once();
        $entityManager->shouldReceive('remove')->with($hangman)->once();

        $classMetadata = \Mockery::mock('\\Doctrine\\ORM\\Mapping\ClassMetadata');

        $mr = new HangmanRepository($entityManager, $classMetadata);
        $mr->save($hangman);
        $mr->delete($hangman);
    }

    /**
     * @test
     */
    public function testCustom() {

        $hangman = \Mockery::mock('\\Hangman\\Hangman');
        $player = \Mockery::mock('\\MiniGame\\Player');
        $player->shouldReceive('getId')->andReturn(42);

        $configuration = \Mockery::mock('\\Doctrine\\ORM\\Configuration');
        $configuration->shouldReceive('getDefaultQueryHints')->andReturn(array());
        $configuration->shouldReceive('isSecondLevelCacheEnabled')->andReturn(false);

        $entityManager = \Mockery::mock('\\Doctrine\\ORM\\EntityManager');
        $entityManager->shouldReceive('getConfiguration')->andReturn($configuration);

        $query = \Mockery::mock(new Query($entityManager));
        $query->shouldReceive('setParameter')->andReturn($query);
        $query->shouldReceive('setMaxResults')->andReturn($query);
        $query->shouldReceive('getSingleResult')->andReturn($hangman);

        $entityManager = \Mockery::mock('\\Doctrine\\ORM\\EntityManager');
        $entityManager->shouldReceive('createQuery')->andReturn($query);

        $classMetadata = \Mockery::mock('\\Doctrine\\ORM\\Mapping\ClassMetadata');

        $mr = new HangmanRepository($entityManager, $classMetadata);
        $hangmanResult = $mr->findPlayerMinigame($player);

        $this->assertEquals($hangman, $hangmanResult);
    }
} 