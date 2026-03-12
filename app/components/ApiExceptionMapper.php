<?php

namespace App\components;

use Application\Livestream\Exception\ActiveLivestreamConflictException;
use Application\Livestream\Exception\ActiveLivestreamNotFoundException;
use Application\Livestream\Exception\InvalidLivestreamInputException;
use Application\Livestream\Exception\LivestreamNotFoundException;
use Throwable;

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
            default => [
                'status' => 500,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Unexpected server error',
            ],
        };
    }
}
