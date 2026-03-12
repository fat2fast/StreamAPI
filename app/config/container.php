<?php
use App\components\ApiExceptionMapper;
use Application\Livestream\Repository\LivestreamRepositoryInterface;
use Infrastructure\Auth\JwtService;
use Infrastructure\Repository\YiiLivestreamRepository;

return [
    'singletons' => [
        ApiExceptionMapper::class => ApiExceptionMapper::class,
        LivestreamRepositoryInterface::class => YiiLivestreamRepository::class,
        JwtService::class => static function () {
            return new JwtService(
                secret: (string) (Yii::$app->params['jwt.secret'] ?? ''),
                issuer: (string) (Yii::$app->params['jwt.issuer'] ?? ''),
                audience: (string) (Yii::$app->params['jwt.audience'] ?? '')
            );
        },
    ],
    'definitions' => [
    ],
];
