<?php

use Slim\App;
use UMA\DIC\Container;
use Crisis\Providers;

/** @var Container $cnt */
$cnt = require_once __DIR__ . '/../bootstrap.php';

$cnt->register(new Providers\Doctrine());
$cnt->register(new Providers\Slim());

/** @var App $app */
$app = $cnt->get(App::class);
$app->run();
