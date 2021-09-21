<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use UMA\DIC\Container;

/** @var Container $container */
$cnt = require_once __DIR__ . '/bootstrap.php';

$cnt->register(new Crisis\Providers\Doctrine());

return ConsoleRunner::createHelperSet($cnt->get(EntityManager::class));