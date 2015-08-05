<?php
namespace Hangman\Test;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Hangman\Hangman;
use Hangman\Repository\HangmanRepository;
use MiniGame\Entity\Player;
use MiniGame\Test\Mock\GameObjectMocker;

class HangmanRepositoryTest extends \PHPUnit_Framework_TestCase
{
    use GameObjectMocker;

    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testSaveDelete()
    {
        /* @var $hangman Hangman */
        $hangman = \Mockery::mock('\\Hangman\\Hangman');

        /* @var $entityManager EntityManager */
        $entityManager = \Mockery::mock('\\Doctrine\\ORM\\EntityManager');
        $entityManager->shouldReceive('persist')->with($hangman)->once();
        $entityManager->shouldReceive('remove')->with($hangman)->once();

        /* @var $classMetadata ClassMetadata */
        $classMetadata = \Mockery::mock('\\Doctrine\\ORM\\Mapping\ClassMetadata');

        $mr = new HangmanRepository($entityManager, $classMetadata);
        $mr->save($hangman);
        $mr->delete($hangman);
    }

    /**
     * @test
     */
    public function testCustom()
    {
        /* @var $hangman Hangman */
        $hangman = \Mockery::mock('\\Hangman\\Hangman');
        $playerId = $this->getPlayerId('42');

        $configuration = \Mockery::mock('\\Doctrine\\ORM\\Configuration');
        $configuration->shouldReceive('getDefaultQueryHints')->andReturn(array());
        $configuration->shouldReceive('isSecondLevelCacheEnabled')->andReturn(false);

        /* @var $entityManager EntityManager */
        $entityManager = \Mockery::mock('\\Doctrine\\ORM\\EntityManager');
        $entityManager->shouldReceive('getConfiguration')->andReturn($configuration);

        $query = \Mockery::mock(new Query($entityManager));
        $query->shouldReceive('setParameter')->andReturn($query);
        $query->shouldReceive('setMaxResults')->andReturn($query);
        $query->shouldReceive('getSingleResult')->andReturn($hangman);

        $entityManager = \Mockery::mock('\\Doctrine\\ORM\\EntityManager');
        $entityManager->shouldReceive('createQuery')->andReturn($query);

        $classMetadata = \Mockery::mock('\\Doctrine\\ORM\\Mapping\ClassMetadata');

        /* @var $classMetadata ClassMetadata */
        $mr = new HangmanRepository($entityManager, $classMetadata);
        $hangmanResult = $mr->findPlayerMinigame($playerId);

        $this->assertEquals($hangman, $hangmanResult);
    }
}
