<?php

namespace App\components;

use Infrastructure\Auth\InvalidTokenException;
use Infrastructure\Auth\JwtService;
use Yii;
use yii\base\ActionFilter;
use yii\web\Response;

final class JwtAuthFilter extends ActionFilter
{
    public ?string $requiredRole = null;

    public function beforeAction($action): bool
    {
        $request = Yii::$app->request;
        $response = Yii::$app->response;

        $authorization = (string) $request->headers->get('Authorization', '');
        if (!preg_match('/^Bearer\s+(.+)$/i', $authorization, $matches)) {
            $this->reject($response, 401, 'UNAUTHORIZED', 'Missing bearer token');
            return false;
        }

        $jwtService = Yii::$container->get(JwtService::class);

        try {
            $claims = $jwtService->decode($matches[1]);
        } catch (InvalidTokenException) {
            $this->reject($response, 401, 'UNAUTHORIZED', 'Invalid or expired token');
            return false;
        }

        if ($this->requiredRole !== null && $claims['role'] !== $this->requiredRole) {
            $this->reject($response, 403, 'FORBIDDEN', 'You are not allowed to access this resource');
            return false;
        }

        /** @var AuthContext $authContext */
        $authContext = Yii::$app->get('authContext');
        $authContext->set(userId: $claims['sub'], role: $claims['role']);

        return parent::beforeAction($action);
    }

    private function reject(Response $response, int $statusCode, string $error, string $message): void
    {
        $response->statusCode = $statusCode;
        $response->data = [
            'error' => $error,
            'message' => $message,
        ];
    }
}
