<?php

namespace App\components;

use Application\Livestream\Exception\ActiveLivestreamConflictException;
use Application\Livestream\Exception\ActiveLivestreamNotFoundException;
use Application\Livestream\Exception\InvalidLivestreamInputException;
use Application\Livestream\Exception\LivestreamNotFoundException;
use Infrastructure\Auth\InvalidTokenException;
use Throwable;
use yii\db\IntegrityException;
use yii\web\HttpException;

final class ApiExceptionMapper
{
    /**
     * @return array{status:int,error:string,message:string}
     */
    public function map(Throwable $throwable): array
    {
        return match (true) {
            $throwable instanceof InvalidLivestreamInputException => [
                'status' => 400,
                'error' => 'BAD_REQUEST',
                'message' => $throwable->getMessage(),
            ],
            $throwable instanceof ActiveLivestreamConflictException => [
                'status' => 409,
                'error' => 'CONFLICT',
                'message' => $throwable->getMessage(),
            ],
            $throwable instanceof IntegrityException
                && str_contains($throwable->getMessage(), 'uq-livestreams-active-streamer-id') => [
                'status' => 409,
                'error' => 'CONFLICT',
                'message' => 'Streamer already has an active session',
            ],
            $throwable instanceof ActiveLivestreamNotFoundException => [
                'status' => 404,
                'error' => 'NOT_FOUND',
                'message' => $throwable->getMessage(),
            ],
            $throwable instanceof LivestreamNotFoundException => [
                'status' => 404,
                'error' => 'NOT_FOUND',
                'message' => 'Livestream not found',
            ],
            $throwable instanceof InvalidTokenException => [
                'status' => 401,
                'error' => 'UNAUTHORIZED',
                'message' => 'Invalid or expired token',
            ],
            $throwable instanceof HttpException => [
                'status' => $throwable->statusCode,
                'error' => $this->errorFromStatus($throwable->statusCode),
                'message' => $throwable->getMessage() !== '' ? $throwable->getMessage() : $this->defaultMessage($throwable->statusCode),
            ],
            default => [
                'status' => 500,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Unexpected server error',
            ],
        };
    }

    private function errorFromStatus(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            409 => 'CONFLICT',
            default => 'INTERNAL_ERROR',
        };
    }

    private function defaultMessage(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'Invalid request payload',
            401 => 'Invalid or expired token',
            403 => 'You are not allowed to access this resource',
            404 => 'Resource not found',
            409 => 'Conflict',
            default => 'Unexpected server error',
        };
    }
}
