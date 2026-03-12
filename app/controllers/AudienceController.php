<?php

namespace App\controllers;

use App\components\JwtAuthFilter;
use Application\Livestream\DTO\GetLivestreamInput;
use Application\Livestream\DTO\ListLivestreamsInput;
use Application\Livestream\UseCase\GetLivestreamUseCase;
use Application\Livestream\UseCase\ListLivestreamsUseCase;
use Throwable;
use Yii;

final class AudienceController extends ApiController
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

            return $this->success(
                data: $payload['data'],
                extra: ['total' => $payload['total']]
            );
        } catch (Throwable $throwable) {
            return $this->fromException($throwable);
        }
    }

    public function actionLivestream(int $livestream_id): array
    {
        /** @var GetLivestreamUseCase $useCase */
        $useCase = Yii::$container->get(GetLivestreamUseCase::class);

        try {
            $output = $useCase(new GetLivestreamInput(livestreamId: $livestream_id));

            return $this->success($output->toArray());
        } catch (Throwable $throwable) {
            return $this->fromException($throwable);
        }
    }
}
