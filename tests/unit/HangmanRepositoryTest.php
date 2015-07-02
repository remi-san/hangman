<?php
namespace Hangman\Test;

use Hangman\Repository\HangmanRepository;

class HangmanRepositoryTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function testWordService() {

        $player = \Mockery::mock('\\Hangman\\Hangman');

        $entityManager = \Mockery::mock('\\Doctrine\\ORM\\EntityManager');
        $entityManager->shouldReceive('persist')->with($player)->once();
        $entityManager->shouldReceive('detach')->with($player)->once();

        $classMetadata = \Mockery::mock('\\Doctrine\\ORM\\Mapping\ClassMetadata');

        $mr = new HangmanRepository($entityManager, $classMetadata);
        $mr->save($player);
        $mr->delete($player);
    }
} 