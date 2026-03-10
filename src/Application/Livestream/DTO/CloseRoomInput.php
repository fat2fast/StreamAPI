<?php

namespace Application\Livestream\DTO;

readonly class CloseRoomInput
{
    public function __construct(public int $streamerId)
    {
    }
}
