<?php

namespace Application\Livestream\DTO;

readonly class StartRoomInput
{
    public function __construct(
        public int $streamerId,
        public string $title
    ) {
    }
}
