<?php

declare(strict_types=1);

namespace Tests\Unit\Components;

use App\components\ApiExceptionMapper;
use Application\Livestream\Exception\ActiveLivestreamConflictException;
use Application\Livestream\Exception\ActiveLivestreamNotFoundException;
use Application\Livestream\Exception\InvalidLivestreamInputException;
use Application\Livestream\Exception\LivestreamNotFoundException;
use Infrastructure\Auth\InvalidTokenException;
use PHPUnit\Framework\TestCase;
use yii\db\IntegrityException;
use yii\web\NotFoundHttpException;

final class ApiExceptionMapperTest extends TestCase
{
    public function testMapsBadRequestException(): void
    {
        $mapper = new ApiExceptionMapper();

        $mapped = $mapper->map(new InvalidLivestreamInputException('Invalid title'));

        self::assertSame(400, $mapped['status']);
        self::assertSame('BAD_REQUEST', $mapped['error']);
        self::assertSame('Invalid title', $mapped['message']);
    }

    public function testMapsConflictException(): void
    {
        $mapper = new ApiExceptionMapper();

        $mapped = $mapper->map(new ActiveLivestreamConflictException());

        self::assertSame(409, $mapped['status']);
        self::assertSame('CONFLICT', $mapped['error']);
    }

    public function testMapsNotFoundExceptions(): void
    {
        $mapper = new ApiExceptionMapper();

        $activeNotFound = $mapper->map(new ActiveLivestreamNotFoundException());
        self::assertSame(404, $activeNotFound['status']);
        self::assertSame('NOT_FOUND', $activeNotFound['error']);

        $livestreamNotFound = $mapper->map(new LivestreamNotFoundException(100));
        self::assertSame(404, $livestreamNotFound['status']);
        self::assertSame('NOT_FOUND', $livestreamNotFound['error']);
        self::assertSame('Livestream not found', $livestreamNotFound['message']);
    }

    public function testMapsUnknownExceptionToInternalError(): void
    {
        $mapper = new ApiExceptionMapper();

        $mapped = $mapper->map(new \RuntimeException('unexpected'));

        self::assertSame(500, $mapped['status']);
        self::assertSame('INTERNAL_ERROR', $mapped['error']);
        self::assertSame('Unexpected server error', $mapped['message']);
    }

    public function testMapsUniqueConstraintConflict(): void
    {
        $mapper = new ApiExceptionMapper();

        $mapped = $mapper->map(new IntegrityException('Duplicate entry for uq-livestreams-active-streamer-id'));

        self::assertSame(409, $mapped['status']);
        self::assertSame('CONFLICT', $mapped['error']);
    }

    public function testMapsInvalidTokenException(): void
    {
        $mapper = new ApiExceptionMapper();

        $mapped = $mapper->map(new InvalidTokenException('token bad'));

        self::assertSame(401, $mapped['status']);
        self::assertSame('UNAUTHORIZED', $mapped['error']);
    }

    public function testMapsHttpException(): void
    {
        $mapper = new ApiExceptionMapper();

        $mapped = $mapper->map(new NotFoundHttpException('Not Found'));

        self::assertSame(404, $mapped['status']);
        self::assertSame('NOT_FOUND', $mapped['error']);
        self::assertSame('Not Found', $mapped['message']);
    }
}
