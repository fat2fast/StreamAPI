<?php

namespace App\commands;

use Firebase\JWT\JWT;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class TokenController extends Controller
{
    public function actionGenerate(int $userId, string $role): int
    {
        if ($userId <= 0) {
            $this->stderr("userId must be greater than zero.\n");
            return ExitCode::DATAERR;
        }

        if (!in_array($role, ['streamer', 'audience'], true)) {
            $this->stderr("role must be 'streamer' or 'audience'.\n");
            return ExitCode::DATAERR;
        }

        $issuedAt = time();
        $ttl = (int) (Yii::$app->params['jwt.ttl'] ?? 3600);

        $payload = [
            'sub' => $userId,
            'role' => $role,
            'iss' => (string) (Yii::$app->params['jwt.issuer'] ?? 'yii2-livestream-api'),
            'aud' => (string) (Yii::$app->params['jwt.audience'] ?? 'livestream-clients'),
            'iat' => $issuedAt,
            'exp' => $issuedAt + $ttl,
        ];

        $secret = (string) (Yii::$app->params['jwt.secret'] ?? '');
        if ($secret === '') {
            $this->stderr("jwt.secret is empty.\n");
            return ExitCode::CONFIG;
        }

        if (strlen($secret) < 32) {
            $this->stderr("jwt.secret must be at least 32 characters for HS256.\n");
            return ExitCode::CONFIG;
        }

        $token = JWT::encode($payload, $secret, 'HS256');
        $this->stdout($token . PHP_EOL);

        return ExitCode::OK;
    }
}
