<?php

namespace Domain\Livestream\Entity;

use DateTimeImmutable;
use Domain\Livestream\Enum\LivestreamStatus;

readonly class Livestream
{
    public function __construct(
        public int $id,
        public int $streamerId,
        public string $title,
        public LivestreamStatus $status,
        public DateTimeImmutable $startedAt,
        public ?DateTimeImmutable $closedAt
    ) {
    }

    public function isActive(): bool
    {
        return $this->status === LivestreamStatus::Active;
    }

    public function isClosed(): bool
    {
        return $this->status === LivestreamStatus::Closed;
    }

    public function close(DateTimeImmutable $closedAt): self
    {
        return new self(
            id: $this->id,
            streamerId: $this->streamerId,
            title: $this->title,
            status: LivestreamStatus::Closed,
            startedAt: $this->startedAt,
            closedAt: $closedAt
        );
    }
}
