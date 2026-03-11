<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';
$container = require __DIR__ . '/container.php';

return [
    'id' => 'yii2-livestream-api',
    'name' => 'Yii2 Livestream API',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@App' => '@app',
    ],
    'controllerNamespace' => 'App\\controllers',
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            'cookieValidationKey' => getenv('COOKIE_VALIDATION_KEY') ?: 'dev-only-cookie-key',
            'parsers' => [
                'application/json' => 'yii\\web\\JsonParser',
            ],
        ],
        'response' => [
            'format' => 'json',
            'charset' => 'UTF-8',
        ],
        'authContext' => [
            'class' => App\components\AuthContext::class,
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'POST streamer/start_room' => 'streamer/start-room',
                'POST streamer/close_room' => 'streamer/close-room',
                'GET audience/livestreams' => 'audience/livestreams',
            ],
        ],
        'errorHandler' => [
            'errorAction' => null,
        ],
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
