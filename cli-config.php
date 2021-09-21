<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use UMA\DIC\Container;
use UMA\DoctrineDemo\DI;

/** @var Container $container */
$container = require_once __DIR__ . '/bootstrap.php';

$cnt->register(new Crisis\Providers\Doctrine());

return ConsoleRunner::createHelperSet($container[EntityManager::class]);