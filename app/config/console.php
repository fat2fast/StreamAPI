<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$container = require __DIR__ . '/container.php';

return [
    'id' => 'yii2-livestream-console',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@App' => '@app',
    ],
    'bootstrap' => ['log'],
    'controllerNamespace' => 'App\\commands',
    'components' => [
        'db' => $db,
        'log' => [
            'traceLevel' => getenv('APP_DEBUG') === '1' ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\\log\\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => $params,
    'container' => $container,
];
