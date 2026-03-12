<?php

declare(strict_types=1);

namespace Tests\Unit\Controllers;

use App\components\AuthContext;
use App\controllers\AudienceController;
use Application\Livestream\DTO\GetLivestreamInput;
use Application\Livestream\DTO\ListLivestreamsInput;
use Application\Livestream\DTO\ListLivestreamsOutput;
use Application\Livestream\DTO\LivestreamOutput;
use Application\Livestream\Exception\LivestreamNotFoundException;
use Application\Livestream\UseCase\GetLivestreamUseCase;
use Application\Livestream\UseCase\ListLivestreamsUseCase;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\Application;

final class AudienceControllerTest extends TestCase
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
        Yii::$container->clear(ListLivestreamsUseCase::class);
        Yii::$container->clear(GetLivestreamUseCase::class);

        restore_error_handler();
        restore_exception_handler();

        Yii::$app = null;

        parent::tearDown();
    }

    public function testLivestreamsReturnsListEnvelope(): void
    {
        Yii::$container->set(ListLivestreamsUseCase::class, static function () {
            return new class {
                public function __invoke(ListLivestreamsInput $input): ListLivestreamsOutput
                {
                    return new ListLivestreamsOutput(
                        items: [
                            new LivestreamOutput(
                                id: 101,
                                streamerId: 1,
                                title: 'Morning coding',
                                status: 'active',
                                startedAt: '2026-03-11T08:00:00+00:00',
                                closedAt: null
                            ),
                            new LivestreamOutput(
                                id: 102,
                                streamerId: 2,
                                title: 'Live Q&A',
                                status: 'active',
                                startedAt: '2026-03-11T09:00:00+00:00',
                                closedAt: null
                            ),
                        ],
                        total: 2
                    );
                }
            };
        });

        $controller = new AudienceController('audience', Yii::$app);
        $result = $controller->actionLivestreams();

        self::assertSame('OK', $result['message']);
        self::assertSame(2, $result['total']);
        self::assertCount(2, $result['data']);
        self::assertSame('Morning coding', $result['data'][0]['title']);
    }

    public function testLivestreamsReturnsInternalErrorWhenUseCaseFails(): void
    {
        Yii::$container->set(ListLivestreamsUseCase::class, static function () {
            return new class {
                public function __invoke(ListLivestreamsInput $input): ListLivestreamsOutput
                {
                    throw new \RuntimeException('DB unavailable');
                }
            };
        });

        $controller = new AudienceController('audience', Yii::$app);
        $result = $controller->actionLivestreams();

        self::assertSame(500, Yii::$app->response->statusCode);
        self::assertSame('INTERNAL_ERROR', $result['error']);
    }

    public function testLivestreamDetailReturnsSuccessEnvelope(): void
    {
        Yii::$container->set(GetLivestreamUseCase::class, static function () {
            return new class {
                public function __invoke(GetLivestreamInput $input): LivestreamOutput
                {
                    return new LivestreamOutput(
                        id: $input->livestreamId,
                        streamerId: 3,
                        title: 'Architecture deep dive',
                        status: 'active',
                        startedAt: '2026-03-11T10:00:00+00:00',
                        closedAt: null
                    );
                }
            };
        });

        $controller = new AudienceController('audience', Yii::$app);
        $result = $controller->actionLivestream(501);

        self::assertSame('OK', $result['message']);
        self::assertSame(501, $result['data']['id']);
        self::assertSame('active', $result['data']['status']);
    }

    public function testLivestreamDetailReturnsNotFoundEnvelope(): void
    {
        Yii::$container->set(GetLivestreamUseCase::class, static function () {
            return new class {
                public function __invoke(GetLivestreamInput $input): LivestreamOutput
                {
                    throw new LivestreamNotFoundException($input->livestreamId);
                }
            };
        });

        $controller = new AudienceController('audience', Yii::$app);
        $result = $controller->actionLivestream(999);

        self::assertSame(404, Yii::$app->response->statusCode);
        self::assertSame('NOT_FOUND', $result['error']);
    }
}
