<?php

namespace Domain\Livestream\Repository;

use Domain\Livestream\Entity\Livestream;

interface LivestreamRepositoryInterface
{
    public function findActiveByStreamerId(int $streamerId): ?Livestream;

    public function findActiveById(int $livestreamId): ?Livestream;

    /**
     * @return list<Livestream>
     */
    public function listActive(): array;

    public function save(Livestream $livestream): Livestream;
}
