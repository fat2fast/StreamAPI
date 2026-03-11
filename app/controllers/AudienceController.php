<?php

namespace App\controllers;

use App\components\JwtAuthFilter;
use Application\Livestream\DTO\GetLivestreamInput;
use Application\Livestream\DTO\ListLivestreamsInput;
use Application\Livestream\Exception\InvalidLivestreamInputException;
use Application\Livestream\Exception\LivestreamNotFoundException;
use Application\Livestream\UseCase\GetLivestreamUseCase;
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

    public function actionLivestream(int $livestream_id): array
    {
        /** @var GetLivestreamUseCase $useCase */
        $useCase = Yii::$container->get(GetLivestreamUseCase::class);

        try {
            $output = $useCase(new GetLivestreamInput(livestreamId: $livestream_id));

            return [
                'data' => $output->toArray(),
                'message' => 'OK',
            ];
        } catch (InvalidLivestreamInputException $exception) {
            Yii::$app->response->statusCode = 400;

            return [
                'error' => 'BAD_REQUEST',
                'message' => $exception->getMessage(),
            ];
        } catch (LivestreamNotFoundException) {
            Yii::$app->response->statusCode = 404;

            return [
                'error' => 'NOT_FOUND',
                'message' => 'Livestream not found',
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
