<?php

namespace Application\Livestream\Exception;

use RuntimeException;

final class LivestreamNotFoundException extends RuntimeException
{
    public function __construct(int $livestreamId)
    {
        parent::__construct(sprintf('Livestream %d not found', $livestreamId));
    }
}
