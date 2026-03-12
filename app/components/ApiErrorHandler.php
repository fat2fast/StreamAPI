<?php

namespace App\components;

use Throwable;
use Yii;
use yii\web\ErrorHandler;
use yii\web\Response;

class ApiErrorHandler extends ErrorHandler
{
    protected function renderException($exception): void
    {
        if (Yii::$app === null) {
            parent::renderException($exception);
            return;
        }

        $response = Yii::$app->getResponse();

        try {
            /** @var ApiExceptionMapper $mapper */
            $mapper = Yii::$container->get(ApiExceptionMapper::class);
            $mapped = $mapper->map($exception instanceof Throwable ? $exception : new \RuntimeException('Unexpected error'));
        } catch (Throwable) {
            $mapped = [
                'status' => 500,
                'error' => 'INTERNAL_ERROR',
                'message' => 'Unexpected server error',
            ];
        }

        $response->format = Response::FORMAT_JSON;
        $response->statusCode = $mapped['status'];
        $response->data = [
            'error' => $mapped['error'],
            'message' => $mapped['message'],
        ];

        $response->send();
    }
}
