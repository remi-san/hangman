<?php
require_once __DIR__.'/vendor/autoload.php';

$dbParams = array(
    "driver"   => "pdo_pgsql",
    "host"     => "172.16.103.129",
    "user"     => "postgres",
    "password" => "postgres",
    "dbname"   => "games",
);

$dbConfig = \Doctrine\ORM\Tools\Setup::createYAMLMetadataConfiguration(
    array("/home/macosx/web/twitter-hangman/vendor/remi-san/hangman/config/orm"),
    true
);
$entityManager = \Doctrine\ORM\EntityManager::create($dbParams, $dbConfig);

$playerRepository  = $entityManager->getRepository('\\Hangman\\Entity\\HangmanPlayer');
$hangmanRepository = $entityManager->getRepository('\\Hangman\\Entity\\Hangman');

$player = new \Hangman\Entity\HangmanPlayer(null, "remi");
$playerRepository->save($player);

$hangman = \Hangman\Entity\Hangman::createGame(null, 'word', array($player));
$hangmanRepository->save($hangman);

$hangman = $hangmanRepository->find($hangman->getId());

$entityManager->flush();

$hangman = $hangmanRepository->findPlayerMinigame($player);

$hangmanRepository->delete($hangman);
$playerRepository->delete($player);

$entityManager->flush();
