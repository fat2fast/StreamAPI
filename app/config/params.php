<?php

return [
    'jwt.secret' => getenv('JWT_SECRET') ?: 'change-this-jwt-secret-min-32-chars',
    'jwt.issuer' => getenv('JWT_ISSUER') ?: 'yii2-livestream-api',
    'jwt.audience' => getenv('JWT_AUDIENCE') ?: 'livestream-clients',
    'jwt.ttl' => (int) (getenv('JWT_TTL') ?: 3600),
];
