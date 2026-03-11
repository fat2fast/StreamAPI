<?php

namespace App\controllers;

use App\components\JwtAuthFilter;
use Application\Livestream\DTO\ListLivestreamsInput;
use Application\Livestream\UseCase\ListLivestreamsUseCase;
use Throwable;
use Yii;
use yii\web\Controller;

final class AudienceController extends Controller
{
    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['jwtAuth'] = [
            'class' => JwtAuthFilter::class,
            'requiredRole' => 'audience',
        ];

        return $behaviors;
    }

    public function actionLivestreams(): array
    {
        /** @var ListLivestreamsUseCase $useCase */
        $useCase = Yii::$container->get(ListLivestreamsUseCase::class);

        try {
            $output = $useCase(new ListLivestreamsInput());
            $payload = $output->toArray();

            return [
                'data' => $payload['data'],
                'total' => $payload['total'],
                'message' => 'OK',
            ];
        } catch (Throwable) {
            Yii::$app->response->statusCode = 500;

            return [
                'error' => 'INTERNAL_ERROR',
                'message' => 'Unexpected server error',
            ];
        }
    }
}
