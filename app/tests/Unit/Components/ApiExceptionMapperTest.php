<?php

declare(strict_types=1);

namespace Tests\Unit\Components;

use App\components\ApiExceptionMapper;
use Application\Livestream\Exception\ActiveLivestreamConflictException;
use Application\Livestream\Exception\ActiveLivestreamNotFoundException;
use Application\Livestream\Exception\InvalidLivestreamInputException;
use Application\Livestream\Exception\LivestreamNotFoundException;
use PHPUnit\Framework\TestCase;

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
}
