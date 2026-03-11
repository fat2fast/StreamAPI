<?php

namespace Application\Livestream\DTO;

readonly class GetLivestreamInput
{
    public function __construct(public int $livestreamId)
    {
    }
}
