<?php
namespace Hangman\Repository;

use Doctrine\ORM\EntityRepository;
use MiniGame\Repository\MiniGameRepository;

class HangmanRepository extends EntityRepository implements MiniGameRepository { }