<?php

namespace Infrastructure\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;
use Throwable;

final class JwtService
{
    public function __construct(
        private readonly string $secret,
        private readonly string $issuer,
        private readonly string $audience
    ) {
    }

    /**
     * @return array{sub:int,role:string,iat:int,exp:int}
     */
    public function decode(string $token): array
    {
        try {
            $payload = JWT::decode($token, new Key($this->secret, 'HS256'));
        } catch (Throwable $throwable) {
            throw new InvalidTokenException('Invalid or expired token', 0, $throwable);
        }

        if (!$payload instanceof stdClass) {
            throw new InvalidTokenException('Invalid token payload');
        }

        if (($payload->iss ?? null) !== $this->issuer || ($payload->aud ?? null) !== $this->audience) {
            throw new InvalidTokenException('Token issuer or audience mismatch');
        }

        $sub = isset($payload->sub) ? (int) $payload->sub : 0;
        $role = isset($payload->role) ? (string) $payload->role : '';
        $iat = isset($payload->iat) ? (int) $payload->iat : 0;
        $exp = isset($payload->exp) ? (int) $payload->exp : 0;

        if ($sub <= 0 || $role === '' || $iat <= 0 || $exp <= 0) {
            throw new InvalidTokenException('Token payload is missing required claims');
        }

        return [
            'sub' => $sub,
            'role' => $role,
            'iat' => $iat,
            'exp' => $exp,
        ];
    }
}
