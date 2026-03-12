<?php

namespace App\controllers;

use App\components\AuthContext;
use App\components\JwtAuthFilter;
use Application\Livestream\DTO\CloseRoomInput;
use Application\Livestream\DTO\StartRoomInput;
use Application\Livestream\UseCase\CloseRoomUseCase;
use Application\Livestream\UseCase\StartRoomUseCase;
use Throwable;
use Yii;

final class StreamerController extends ApiController
{
    public $enableCsrfValidation = false;

    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['jwtAuth'] = [
            'class' => JwtAuthFilter::class,
            'requiredRole' => 'streamer',
        ];

        return $behaviors;
    }

    public function actionStartRoom(): array
    {
        /** @var AuthContext $authContext */
        $authContext = Yii::$app->get('authContext');
        $streamerId = (int) ($authContext->userId() ?? 0);

        $title = (string) Yii::$app->request->getBodyParam('title', '');

        /** @var StartRoomUseCase $useCase */
        $useCase = Yii::$container->get(StartRoomUseCase::class);

        try {
            $output = $useCase(new StartRoomInput(
                streamerId: $streamerId,
                title: $title
            ));

            return $this->success($output->toArray(), 201);
        } catch (Throwable $throwable) {
            return $this->fromException($throwable);
        }
    }

    public function actionCloseRoom(): array
    {
        /** @var AuthContext $authContext */
        $authContext = Yii::$app->get('authContext');
        $streamerId = (int) ($authContext->userId() ?? 0);

        /** @var CloseRoomUseCase $useCase */
        $useCase = Yii::$container->get(CloseRoomUseCase::class);

        try {
            $output = $useCase(new CloseRoomInput(streamerId: $streamerId));

            return $this->success($output->toArray());
        } catch (Throwable $throwable) {
            return $this->fromException($throwable);
        }
    }
}
