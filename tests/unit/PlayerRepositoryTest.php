<?php
namespace Hangman\Test;

use Hangman\Repository\HangmanPlayerRepository;
use MiniGame\Entity\Player;

class PlayerRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @test
     */
    public function testSaveDelete()
    {
        /* @var $player Player */
        $player = \Mockery::mock('\\Hangman\\Entity\\HangmanPlayer');

        $entityManager = \Mockery::mock('\\Doctrine\\ORM\\EntityManager');
        $entityManager->shouldReceive('persist')->with($player)->once();
        $entityManager->shouldReceive('remove')->with($player)->once();
        $entityManager->shouldReceive('flush')->once();

        $classMetadata = \Mockery::mock('\\Doctrine\\ORM\\Mapping\ClassMetadata');

        $mr = new HangmanPlayerRepository($entityManager, $classMetadata);
        $mr->save($player);
        $mr->delete($player);
    }
}
