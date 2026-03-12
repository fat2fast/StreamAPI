<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\components\AuthContext;
use App\controllers\StreamerController;
use Application\Livestream\DTO\CloseRoomInput;
use Application\Livestream\DTO\LivestreamOutput;
use Application\Livestream\DTO\StartRoomInput;
use Application\Livestream\Exception\ActiveLivestreamConflictException;
use Application\Livestream\Exception\ActiveLivestreamNotFoundException;
use Application\Livestream\UseCase\CloseRoomUseCase;
use Application\Livestream\UseCase\StartRoomUseCase;
use DateTimeImmutable;
use Yii;
use yii\web\Application;
use PHPUnit\Framework\TestCase;

final class StreamerControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        new Application([
            'id' => 'test-app',
            'basePath' => dirname(__DIR__, 3),
            'controllerNamespace' => 'App\\controllers',
            'components' => [
                'request' => [
                    'class' => 'yii\\web\\Request',
                    'cookieValidationKey' => 'test-key',
                    'scriptFile' => __FILE__,
                    'scriptUrl' => '/index-test.php',
                ],
                'response' => [
                    'class' => 'yii\\web\\Response',
                    'format' => 'json',
                ],
                'authContext' => [
                    'class' => AuthContext::class,
                ],
            ],
        ]);
    }

    protected function tearDown(): void
    {
        Yii::$container->clear(StartRoomUseCase::class);
        Yii::$container->clear(CloseRoomUseCase::class);

        restore_error_handler();
        restore_exception_handler();

        Yii::$app = null;

        parent::tearDown();
    }

    public function testStartRoomReturnsCreatedEnvelope(): void
    {
        Yii::$container->set(StartRoomUseCase::class, static function () {
            return new class {
                public function __invoke(StartRoomInput $input): LivestreamOutput
                {
                    return new LivestreamOutput(
                        id: 101,
                        streamerId: $input->streamerId,
                        title: $input->title,
                        status: 'active',
                        startedAt: (new DateTimeImmutable('2026-03-10T10:00:00+00:00'))->format(DATE_ATOM),
                        closedAt: null
                    );
                }
            };
        });

        /** @var AuthContext $authContext */
        $authContext = Yii::$app->get('authContext');
        $authContext->set(9, 'streamer');

        Yii::$app->request->setBodyParams(['title' => 'Refactoring stream']);

        $controller = new StreamerController('streamer', Yii::$app);
        $result = $controller->actionStartRoom();

        self::assertSame(201, Yii::$app->response->statusCode);
        self::assertSame('OK', $result['message']);
        self::assertSame('active', $result['data']['status']);
        self::assertSame(9, $result['data']['streamer_id']);
    }

    public function testStartRoomReturnsConflictEnvelope(): void
    {
        Yii::$container->set(StartRoomUseCase::class, static function () {
            return new class {
                public function __invoke(StartRoomInput $input): LivestreamOutput
                {
                    throw new ActiveLivestreamConflictException();
                }
            };
        });

        /** @var AuthContext $authContext */
        $authContext = Yii::$app->get('authContext');
        $authContext->set(9, 'streamer');

        Yii::$app->request->setBodyParams(['title' => 'Duplicate stream']);

        $controller = new StreamerController('streamer', Yii::$app);
        $result = $controller->actionStartRoom();

        self::assertSame(409, Yii::$app->response->statusCode);
        self::assertSame('CONFLICT', $result['error']);
    }

    public function testCloseRoomReturnsSuccessEnvelope(): void
    {
        Yii::$container->set(CloseRoomUseCase::class, static function () {
            return new class {
                public function __invoke(CloseRoomInput $input): LivestreamOutput
                {
                    return new LivestreamOutput(
                        id: 101,
                        streamerId: $input->streamerId,
                        title: 'Refactoring stream',
                        status: 'closed',
                        startedAt: (new DateTimeImmutable('2026-03-10T10:00:00+00:00'))->format(DATE_ATOM),
                        closedAt: (new DateTimeImmutable('2026-03-10T11:00:00+00:00'))->format(DATE_ATOM)
                    );
                }
            };
        });

        /** @var AuthContext $authContext */
        $authContext = Yii::$app->get('authContext');
        $authContext->set(9, 'streamer');

        $controller = new StreamerController('streamer', Yii::$app);
        $result = $controller->actionCloseRoom();

        self::assertSame(200, Yii::$app->response->statusCode);
        self::assertSame('OK', $result['message']);
        self::assertSame('closed', $result['data']['status']);
    }

    public function testCloseRoomReturnsNotFoundEnvelope(): void
    {
        Yii::$container->set(CloseRoomUseCase::class, static function () {
            return new class {
                public function __invoke(CloseRoomInput $input): LivestreamOutput
                {
                    throw new ActiveLivestreamNotFoundException();
                }
            };
        });

        /** @var AuthContext $authContext */
        $authContext = Yii::$app->get('authContext');
        $authContext->set(9, 'streamer');

        $controller = new StreamerController('streamer', Yii::$app);
        $result = $controller->actionCloseRoom();

        self::assertSame(404, Yii::$app->response->statusCode);
        self::assertSame('NOT_FOUND', $result['error']);
    }
}
