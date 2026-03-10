<?php

namespace Application\Livestream\Exception;

use RuntimeException;

final class ActiveLivestreamConflictException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Streamer already has an active session');
    }
}
