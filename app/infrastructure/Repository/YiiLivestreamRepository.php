<?php

namespace Infrastructure\Repository;

use App\models\LivestreamRecord;
use DateTimeImmutable;
use Domain\Livestream\Entity\Livestream;
use Domain\Livestream\Enum\LivestreamStatus;
use Domain\Livestream\Repository\LivestreamRepositoryInterface;
use RuntimeException;

class YiiLivestreamRepository implements LivestreamRepositoryInterface
{
    public function findActiveByStreamerId(int $streamerId): ?Livestream
    {
        $record = LivestreamRecord::find()
            ->where([
                'streamer_id' => $streamerId,
                'status' => LivestreamRecord::STATUS_ACTIVE,
            ])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        return $record === null ? null : $this->mapToEntity($record);
    }

    public function findActiveById(int $livestreamId): ?Livestream
    {
        $record = LivestreamRecord::find()
            ->where([
                'id' => $livestreamId,
                'status' => LivestreamRecord::STATUS_ACTIVE,
            ])
            ->one();

        return $record === null ? null : $this->mapToEntity($record);
    }

    public function listActive(): array
    {
        $records = LivestreamRecord::find()
            ->where(['status' => LivestreamRecord::STATUS_ACTIVE])
            ->orderBy(['started_at' => SORT_DESC])
            ->all();

        return array_map(fn (LivestreamRecord $record): Livestream => $this->mapToEntity($record), $records);
    }

    public function save(Livestream $livestream): Livestream
    {
        $record = $livestream->id > 0
            ? LivestreamRecord::findOne($livestream->id)
            : new LivestreamRecord();

        if ($record === null) {
            throw new RuntimeException(sprintf('Livestream record %d not found for update', $livestream->id));
        }

        $record->streamer_id = $livestream->streamerId;
        $record->title = $livestream->title;
        $record->status = $livestream->status->value;
        $record->started_at = $livestream->startedAt->format('Y-m-d H:i:s');
        $record->closed_at = $livestream->closedAt?->format('Y-m-d H:i:s');

        if (!$record->save()) {
            throw new RuntimeException('Failed to save livestream record');
        }

        return $this->mapToEntity($record);
    }

    private function mapToEntity(LivestreamRecord $record): Livestream
    {
        return new Livestream(
            id: (int) $record->id,
            streamerId: (int) $record->streamer_id,
            title: (string) $record->title,
            status: LivestreamStatus::from((string) $record->status),
            startedAt: new DateTimeImmutable((string) $record->started_at),
            closedAt: $record->closed_at !== null
                ? new DateTimeImmutable((string) $record->closed_at)
                : null
        );
    }
}
