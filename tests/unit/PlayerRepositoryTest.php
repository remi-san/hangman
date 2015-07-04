<?php
namespace Hangman\Test;

use Hangman\Repository\HangmanPlayerRepository;

class PlayerRepositoryTest extends \PHPUnit_Framework_TestCase {

    /**
     * @test
     */
    public function testWordService() {

        $player = \Mockery::mock('\\Hangman\\HangmanPlayer');

        $entityManager = \Mockery::mock('\\Doctrine\\ORM\\EntityManager');
        $entityManager->shouldReceive('persist')->with($player)->once();
        $entityManager->shouldReceive('remove')->with($player)->once();

        $classMetadata = \Mockery::mock('\\Doctrine\\ORM\\Mapping\ClassMetadata');

        $mr = new HangmanPlayerRepository($entityManager, $classMetadata);
        $mr->save($player);
        $mr->delete($player);
    }
} 