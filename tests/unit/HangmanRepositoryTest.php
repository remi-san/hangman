<?php
namespace Hangman\Test;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Hangman\Entity\Hangman;
use Hangman\Repository\HangmanRepository;
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
        $hangman = \Mockery::mock('\\Hangman\\Entity\\Hangman');

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
}
