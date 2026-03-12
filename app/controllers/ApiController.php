<?php

namespace App\controllers;

use App\components\ApiExceptionMapper;
use Throwable;
use Yii;
use yii\web\Controller;

abstract class ApiController extends Controller
{
    /**
     * @param array<string,mixed> $data
     * @param array<string,mixed> $extra
     * @return array<string,mixed>
     */
    protected function success(array $data, int $statusCode = 200, array $extra = []): array
    {
        Yii::$app->response->statusCode = $statusCode;

        return array_merge([
            'data' => $data,
            'message' => 'OK',
        ], $extra);
    }

    /**
     * @return array{error:string,message:string}
     */
    protected function fromException(Throwable $throwable): array
    {
        /** @var ApiExceptionMapper $mapper */
        $mapper = Yii::$container->get(ApiExceptionMapper::class);
        $mapped = $mapper->map($throwable);

        Yii::$app->response->statusCode = $mapped['status'];

        return [
            'error' => $mapped['error'],
            'message' => $mapped['message'],
        ];
    }
}
