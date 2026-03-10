<?php

namespace Domain\Livestream\Enum;

enum LivestreamStatus: string
{
    case Active = 'active';
    case Closed = 'closed';
}
