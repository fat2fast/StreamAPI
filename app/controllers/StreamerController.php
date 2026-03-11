<?php

namespace App\controllers;

use App\components\AuthContext;
use App\components\JwtAuthFilter;
use Application\Livestream\DTO\CloseRoomInput;
use Application\Livestream\DTO\StartRoomInput;
use Application\Livestream\Exception\ActiveLivestreamConflictException;
use Application\Livestream\Exception\ActiveLivestreamNotFoundException;
use Application\Livestream\Exception\InvalidLivestreamInputException;
use Application\Livestream\UseCase\CloseRoomUseCase;
use Application\Livestream\UseCase\StartRoomUseCase;
use Throwable;
use Yii;
use yii\web\Controller;

final class StreamerController extends Controller
{
    public bool $enableCsrfValidation = false;

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

            Yii::$app->response->statusCode = 201;
            return [
                'data' => $output->toArray(),
                'message' => 'OK',
            ];
        } catch (InvalidLivestreamInputException $exception) {
            return $this->error(400, 'BAD_REQUEST', $exception->getMessage());
        } catch (ActiveLivestreamConflictException $exception) {
            return $this->error(409, 'CONFLICT', $exception->getMessage());
        } catch (Throwable) {
            return $this->error(500, 'INTERNAL_ERROR', 'Unexpected server error');
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

            Yii::$app->response->statusCode = 200;
            return [
                'data' => $output->toArray(),
                'message' => 'OK',
            ];
        } catch (InvalidLivestreamInputException $exception) {
            return $this->error(400, 'BAD_REQUEST', $exception->getMessage());
        } catch (ActiveLivestreamNotFoundException $exception) {
            return $this->error(404, 'NOT_FOUND', $exception->getMessage());
        } catch (Throwable) {
            return $this->error(500, 'INTERNAL_ERROR', 'Unexpected server error');
        }
    }

    private function error(int $statusCode, string $error, string $message): array
    {
        Yii::$app->response->statusCode = $statusCode;

        return [
            'error' => $error,
            'message' => $message,
        ];
    }
}
