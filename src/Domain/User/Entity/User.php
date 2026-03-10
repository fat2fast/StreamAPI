<?php

namespace Domain\User\Entity;

readonly class User
{
    public function __construct(
        public int $id,
        public string $username,
        public string $email,
        public string $role
    ) {
    }

    public function isStreamer(): bool
    {
        return $this->role === 'streamer';
    }

    public function isAudience(): bool
    {
        return $this->role === 'audience';
    }
}
