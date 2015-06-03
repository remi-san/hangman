<?php
namespace Hangman\Repository;

use Doctrine\ORM\EntityRepository;
use MiniGame\Repository\PlayerRepository;

class HangmanPlayerRepository extends EntityRepository implements PlayerRepository { }