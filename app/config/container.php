<?php

return [
    'singletons' => [
        'Domain\\Livestream\\Repository\\LivestreamRepositoryInterface' => 'Infrastructure\\Repository\\YiiLivestreamRepository',
        'Infrastructure\\Auth\\JwtService' => static function () {
            return new Infrastructure\Auth\JwtService(
                secret: (string) (Yii::$app->params['jwt.secret'] ?? ''),
                issuer: (string) (Yii::$app->params['jwt.issuer'] ?? ''),
                audience: (string) (Yii::$app->params['jwt.audience'] ?? '')
            );
        },
    ],
    'definitions' => [
    ],
];
