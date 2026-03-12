<?php

declare(strict_types=1);

namespace Tests\Functional;

use App\components\AuthContext;
use Application\Livestream\DTO\CloseRoomInput;
use Application\Livestream\DTO\GetLivestreamInput;
use Application\Livestream\DTO\ListLivestreamsInput;
use Application\Livestream\DTO\ListLivestreamsOutput;
use Application\Livestream\DTO\LivestreamOutput;
use Application\Livestream\DTO\StartRoomInput;
use Application\Livestream\Exception\ActiveLivestreamConflictException;
use Application\Livestream\Exception\ActiveLivestreamNotFoundException;
use Application\Livestream\Exception\LivestreamNotFoundException;
use Application\Livestream\UseCase\CloseRoomUseCase;
use Application\Livestream\UseCase\GetLivestreamUseCase;
use Application\Livestream\UseCase\ListLivestreamsUseCase;
use Application\Livestream\UseCase\StartRoomUseCase;
use Infrastructure\Auth\InvalidTokenException;
use Infrastructure\Auth\JwtService;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\web\Application;

final class ApiContractTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        new Application([
            'id' => 'functional-test-app',
            'basePath' => dirname(__DIR__, 2),
            'controllerNamespace' => 'App\\controllers',
            'components' => [
                'request' => [
                    'class' => 'yii\\web\\Request',
                    'cookieValidationKey' => 'functional-test-key',
                    'scriptFile' => __FILE__,
                    'scriptUrl' => '/index-test.php',
                    'parsers' => [
                        'application/json' => 'yii\\web\\JsonParser',
                    ],
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
        Yii::$container->clear(JwtService::class);
        Yii::$container->clear(StartRoomUseCase::class);
        Yii::$container->clear(CloseRoomUseCase::class);
        Yii::$container->clear(ListLivestreamsUseCase::class);
        Yii::$container->clear(GetLivestreamUseCase::class);

        restore_error_handler();
        restore_exception_handler();

        Yii::$app = null;

        parent::tearDown();
    }

    public function testStartRoomReturns401WhenTokenIsMissing(): void
    {
        $this->setRequestHeaders([]);
        Yii::$app->request->setBodyParams(['title' => 'No token stream']);

        Yii::$app->runAction('streamer/start-room');

        self::assertSame(401, Yii::$app->response->statusCode);
        self::assertSame('UNAUTHORIZED', Yii::$app->response->data['error']);
    }

    public function testAudienceListReturns403ForStreamerRole(): void
    {
        $this->setJwtClaims(['sub' => 1, 'role' => 'streamer', 'iat' => time(), 'exp' => time() + 3600]);
        $this->setRequestHeaders(['Authorization' => 'Bearer valid-token']);

        Yii::$app->runAction('audience/livestreams');

        self::assertSame(403, Yii::$app->response->statusCode);
        self::assertSame('FORBIDDEN', Yii::$app->response->data['error']);
    }

    public function testStartRoomReturns409ForActiveSessionConflict(): void
    {
        $this->setJwtClaims(['sub' => 5, 'role' => 'streamer', 'iat' => time(), 'exp' => time() + 3600]);
        $this->setRequestHeaders(['Authorization' => 'Bearer valid-token']);
        Yii::$app->request->setBodyParams(['title' => 'Duplicate stream']);

        Yii::$container->set(StartRoomUseCase::class, static function () {
            return new class {
                public function __invoke(StartRoomInput $input): LivestreamOutput
                {
                    throw new ActiveLivestreamConflictException();
                }
            };
        });

        $result = Yii::$app->runAction('streamer/start-room');

        self::assertSame(409, Yii::$app->response->statusCode);
        self::assertSame('CONFLICT', $result['error']);
    }

    public function testCloseRoomReturns404WhenNoActiveSession(): void
    {
        $this->setJwtClaims(['sub' => 5, 'role' => 'streamer', 'iat' => time(), 'exp' => time() + 3600]);
        $this->setRequestHeaders(['Authorization' => 'Bearer valid-token']);

        Yii::$container->set(CloseRoomUseCase::class, static function () {
            return new class {
                public function __invoke(CloseRoomInput $input): LivestreamOutput
                {
                    throw new ActiveLivestreamNotFoundException();
                }
            };
        });

        $result = Yii::$app->runAction('streamer/close-room');

        self::assertSame(404, Yii::$app->response->statusCode);
        self::assertSame('NOT_FOUND', $result['error']);
    }

    public function testAudienceDetailReturns404ForMissingLivestream(): void
    {
        $this->setJwtClaims(['sub' => 2, 'role' => 'audience', 'iat' => time(), 'exp' => time() + 3600]);
        $this->setRequestHeaders(['Authorization' => 'Bearer valid-token']);

        Yii::$container->set(GetLivestreamUseCase::class, static function () {
            return new class {
                public function __invoke(GetLivestreamInput $input): LivestreamOutput
                {
                    throw new LivestreamNotFoundException($input->livestreamId);
                }
            };
        });

        $result = Yii::$app->runAction('audience/livestream', ['livestream_id' => 999]);

        self::assertSame(404, Yii::$app->response->statusCode);
        self::assertSame('NOT_FOUND', $result['error']);
    }

    public function testAudienceListReturns200WithListEnvelope(): void
    {
        $this->setJwtClaims(['sub' => 2, 'role' => 'audience', 'iat' => time(), 'exp' => time() + 3600]);
        $this->setRequestHeaders(['Authorization' => 'Bearer valid-token']);

        Yii::$container->set(ListLivestreamsUseCase::class, static function () {
            return new class {
                public function __invoke(ListLivestreamsInput $input): ListLivestreamsOutput
                {
                    return new ListLivestreamsOutput(
                        items: [
                            new LivestreamOutput(
                                id: 44,
                                streamerId: 2,
                                title: 'Live coding',
                                status: 'active',
                                startedAt: '2026-03-11T10:00:00+00:00',
                                closedAt: null
                            ),
                        ],
                        total: 1
                    );
                }
            };
        });

        $result = Yii::$app->runAction('audience/livestreams');

        self::assertSame(200, Yii::$app->response->statusCode);
        self::assertSame(1, $result['total']);
        self::assertSame('OK', $result['message']);
    }

    /**
     * @param array{sub:int,role:string,iat:int,exp:int} $claims
     */
    private function setJwtClaims(array $claims): void
    {
        Yii::$container->set(JwtService::class, new class($claims) {
            public function __construct(private readonly array $claims)
            {
            }

            public function decode(string $token): array
            {
                if ($token === 'expired-token') {
                    throw new InvalidTokenException('Invalid or expired token');
                }

                return $this->claims;
            }
        });
    }

    /**
     * @param array<string,string> $headers
     */
    private function setRequestHeaders(array $headers): void
    {
        $requestHeaders = Yii::$app->request->headers;

        foreach ($requestHeaders->toArray() as $name => $_value) {
            $requestHeaders->remove($name);
        }

        foreach ($headers as $name => $value) {
            $requestHeaders->set($name, $value);
        }
    }
}
