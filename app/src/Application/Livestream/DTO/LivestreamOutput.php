<?php

namespace Application\Livestream\DTO;

use Domain\Livestream\Entity\Livestream;

readonly class LivestreamOutput
{
    public function __construct(
        public int $id,
        public int $streamerId,
        public string $title,
        public string $status,
        public string $startedAt,
        public ?string $closedAt
    ) {
    }

    public static function fromEntity(Livestream $livestream): self
    {
        return new self(
            id: $livestream->id,
            streamerId: $livestream->streamerId,
            title: $livestream->title,
            status: $livestream->status->value,
            startedAt: $livestream->startedAt->format(DATE_ATOM),
            closedAt: $livestream->closedAt?->format(DATE_ATOM)
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'streamer_id' => $this->streamerId,
            'title' => $this->title,
            'status' => $this->status,
            'started_at' => $this->startedAt,
            'closed_at' => $this->closedAt,
        ];
    }
}
