<?php

const APP_ROOT = __DIR__;
const TEMPLATES_DIR = __DIR__ . '/php/templates';

$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->load();

return [
    'settings' => [
        'slim' => [
            // Returns a detailed HTML page with error details and
            // a stack trace. Should be disabled in production.
            'displayErrorDetails' => true,

            // Whether to display errors on the internal PHP log or not.
            'logErrors' => true,

            // If true, display full errors with message and stack trace on the PHP log.
            // If false, display only "Slim Application Error" on the PHP log.
            // Doesn't do anything when 'logErrors' is false.
            'logErrorDetails' => true
        ],

        'doctrine' => [
            // if true, metadata caching is forcefully disabled
            'dev_mode' => ($_ENV['PHP_ENV'] != "production"),

            // path where the compiled metadata info will be cached
            // make sure the path exists and it is writable
            'cache_dir' => APP_ROOT . '/var/doctrine',

            // you should add any other path containing annotated entity classes
            'metadata_dirs' => [APP_ROOT . '/php/Models'],

            'connection' => [
                'driver' => $_ENV['DB_DRIVER'],
                'user'     => $_ENV['DB_USER'],
                'password' => $_ENV['DB_PASSWORD'],
                'dbname'   => $_ENV['DB_NAME'],
                'host' => $_ENV['DB_HOST'],
                'port' => $_ENV['DB_PORT'],
            ]
        ],

        'socket' => [
            // Apache config depends on that
            'port' => 8090
        ]
    ]
];
