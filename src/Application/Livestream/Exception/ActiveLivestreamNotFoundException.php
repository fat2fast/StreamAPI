<?php

namespace Application\Livestream\Exception;

use RuntimeException;

final class ActiveLivestreamNotFoundException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Active livestream not found');
    }
}
